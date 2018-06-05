<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Api\Service;

use MauticPlugin\MauticRecombeeBundle\Api\RecombeeApi;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken;
use Psr\Log\LoggerInterface;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\Translation\TranslatorInterface;

class ApiCommands
{
    private $interactionRequiredParams = [
        'AddCartAddition' => ['itemId', 'amount', 'price'],
        'AddPurchase'     => ['itemId', 'amount', 'price', 'profit'],
        'AddDetailView'   => ['itemId'],
        'AddBookmark'     => ['itemId'],
        'AddRating'       => ['itemId', 'rating'],
        'SetViewPortion'  => ['itemId', 'portion'],
    ];

    private $commandOutput;

    /**
     * @var RecombeeApi
     */
    private $recombeeApi;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SegmentMapping
     */
    protected $segmentMapping;

    /**
     * ApiCommands constructor.
     *
     * @param RecombeeApi         $recombeeApi
     * @param LoggerInterface     $logger
     * @param TranslatorInterface $translator
     * @param SegmentMapping      $segmentMapping
     */
    public function __construct(
        RecombeeApi $recombeeApi,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        SegmentMapping $segmentMapping
    ) {

        $this->recombeeApi    = $recombeeApi;
        $this->logger         = $logger;
        $this->translator     = $translator;
        $this->segmentMapping = $segmentMapping;
    }

    private function optionsChecker($apiRequest, $options)
    {
        $options                   = array_keys($options);
        $interactionRequiredParams = $this->getInteractionRequiredParam($apiRequest);
        if (!isset($interactionRequiredParams['userId'])) {
            $interactionRequiredParams = array_merge(['userId'], $interactionRequiredParams);
        }
        //required params no contains from input
        if (count(array_intersect($options, $interactionRequiredParams)) != count($interactionRequiredParams)) {
            $this->logger->error(
                $this->translator->trans(
                    'mautic.plugin.recombee.api.wrong.params',
                    ['%params' => implode(', ', $options), '%options' => implode(',', $interactionRequiredParams)]
                )
            );

            return false;
        }

        return true;
    }

    /**
     * @param       $apiRequest
     * @param array $batchOptions
     */
    public function callCommand($apiRequest, array $batchOptions)
    {
        if (false === $this->optionsChecker($apiRequest, $batchOptions)) {
            //   return false;
        }
        // not batch
        if (!isset($batchOptions[0])) {
            $batchOptions = [$batchOptions];
        }
        $command  = 'Recombee\RecommApi\Requests\\'.$apiRequest;
        $requests = [];
        foreach ($batchOptions as $options) {
            $userId = $options['userId'];
            unset($options['userId']);
            $itemId = $options['itemId'];
            unset($options['itemId']);
            switch ($apiRequest) {
                case "AddDetailView":
                case "AddPurchase":
                case "AddCartAddition":
                case "AddBookmark":
                    $requests[] = new $command(
                        $userId,
                        $itemId,
                        $options
                    );
                    break;
                case "AddRating":
                    $rating = $options['rating'];
                    unset($options['rating']);
                    $requests[] = new $command(
                        $userId,
                        $itemId,
                        $rating,
                        $options
                    );
                    break;
                case "SetViewPortion":
                    $portion = $options['portion'];
                    unset($options['portion']);
                    $requests[] = new $command(
                        $userId,
                        $itemId,
                        $portion,
                        $options
                    );

                    break;
                case "RecommendItemsToUser":
                    $limit = $options['limit'];
                    unset($options['limit']);
                    $requests[] = new $command(
                        $userId,
                        $limit,
                        $options
                    );
                    break;
            }
            //$this->segmentMapping->map($apiRequest, $userId);
        }


        //$this->logger->debug('Recombee requests:'.var_dump($batchOptions));
        try {
            //batch processing
            if (count($requests) > 1) {
                $this->setCommandOutput($this->recombeeApi->getClient()->send(new Reqs\Batch($requests)));
            } elseif (count($requests) == 1) {
                $this->setCommandOutput($this->recombeeApi->getClient()->send(end($requests)));
            }
            //$this->logger->debug('Recombee results:'.var_dump($this->getCommandOutput()));
        } catch (Ex\ResponseException $e) {

            die($e->getMessage());
            $this->logger->error(
                $this->translator->trans(
                    'mautic.plugin.recombee.api.error',
                    ['%error' => $e->getMessage()]
                )
            );
        }
    }

    public function ImportUser($items)
    {
        $processedData = new ProcessData($items, 'AddUserProperty', 'SetUserValues');
        $this->callApi($processedData->getRequestsPropertyName());
        $this->callApi($processedData->getRequestsPropertyValues());
    }

    public function ImportItems($items)
    {
        $processedData = new ProcessData($items, 'AddItemProperty', 'SetItemValues');
        $this->callApi($processedData->getRequestsPropertyName());
        $this->callApi($processedData->getRequestsPropertyValues());
    }

    public function getAbandonedCart(RecombeeToken $recombeeToken, $cartMinAge, $cartMaxAge)
    {
        $options = [
            "expertSettings" => [
                "algorithmSettings" => [
                    "evaluator" => [
                        "name" => "reql",
                    ],
                    "model"     => [
                        "name"     => "reminder",
                        "settings" => [
                            "parameters" => [
                                "interaction-types"        => [
                                    "cart-addition" => [
                                        "enabled" => true,
                                        "weight"  => 1.0,
                                        "min-age" => $cartMinAge,
                                        "max-age" => $cartMaxAge,
                                    ],
                                ],
                                "filter-purchased-max-age" => $cartMaxAge,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $recombeeToken->setAddOptions($options);
        $this->callCommand('RecommendItemsToUser', $recombeeToken->getOptions(true));
    }

    public function callApi($requests)
    {
        if (empty($requests)) {
            return;
        }

        if (!isset($requests[0])) {
            $requests = [$requests];
        }
        try {
            //batch processing
            if (count($requests) > 1) {
                $this->setCommandOutput($this->recombeeApi->getClient()->send(new Reqs\Batch($requests)));
            } elseif (count($requests) == 1) {
                $this->setCommandOutput($this->recombeeApi->getClient()->send(end($requests)));
            }
        } catch (Ex\ResponseException $e) {
            throw new \Exception($e->getMessage());
            /* $this->logger->error(
                 $this->translator->trans(
                     'mautic.plugin.recombee.api.error',
                     ['%error' => $e->getMessage()]
                 )
             );*/
        }
    }

    /**
     * @return mixed
     */
    public function getCommandOutput()
    {
        return $this->commandOutput;
    }

    /**
     * @param mixed $commandOutput
     */
    public function hasCommandOutput()
    {
        if (!empty($this->commandOutput)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $commandOutput
     */
    public function setCommandOutput(
        $commandOutput
    ) {
        $this->commandOutput = $commandOutput;
    }

    /**
     * @return array
     */
    public function getInteractionRequiredParam(
        $key
    ) {
        return $this->interactionRequiredParams[$key];
    }

    public function getCommandResult()
    {
        $errors  = [];
        $results = $this->getCommandOutput();
        if (is_array($results)) {
            foreach ($results as $result) {
                if (!empty($result['json']['error'])) {
                    $errors[] = $result['json']['error'];
                }
            }
        }
        if (!empty($errors)) {
            throw new \Exception($errors);
        }

        return true;
    }

    /**
     * Display commands results
     *
     * @param array  $results
     * @param string $title
     */
    private function displayCmdTextFromResult(
        array $results,
        $title = '',
        OutputInterface $output
    ) {
        $errors = [];
        foreach ($results as $result) {
            if (!empty($result['json']['error'])) {
                $errors[] = $result['json']['error'];
            }
        }
        // just add empty space
        if ($title != '') {
            $title .= ' ';
        }
        $errors = [];
        $output->writeln(sprintf('<info>Procesed '.$title.count($results).'</info>'));
        $output->writeln('Success '.$title.(count($results) - count($errors)));
        /*if (!empty($errors)) {
            $output->writeln('Errors '.$title.count($errors));
            $output->writeln($errors, true);
        }*/
    }

}

