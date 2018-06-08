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
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenFinder;
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

    /**
     * @var array
     */
    private $commandOutput = [];

    /**
     * @var md5
     */
    private $cacheId;

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
     * @var RecombeeTokenFinder
     */
    private $recombeeTokenFinder;

    /**
     * ApiCommands constructor.
     *
     * @param RecombeeApi         $recombeeApi
     * @param LoggerInterface     $logger
     * @param TranslatorInterface $translator
     * @param SegmentMapping      $segmentMapping
     * @param RecombeeTokenFinder $recombeeTokenFinder
     */
    public function __construct(
        RecombeeApi $recombeeApi,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        SegmentMapping $segmentMapping,
        RecombeeTokenFinder $recombeeTokenFinder
    ) {

        $this->recombeeApi         = $recombeeApi;
        $this->logger              = $logger;
        $this->translator          = $translator;
        $this->segmentMapping      = $segmentMapping;
        $this->recombeeTokenFinder = $recombeeTokenFinder;
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
        // not batch
        if (!isset($batchOptions[0])) {
            $batchOptions = [$batchOptions];
        }
        $command  = 'Recombee\RecommApi\Requests\\'.$apiRequest;
        $requests = [];
        foreach ($batchOptions as $options) {
            $userId = $options['userId'];
            unset($options['userId']);
            if (isset($options['itemId'])) {
                $itemId = $options['itemId'];
                unset($options['itemId']);
            }
            $req = '';
            switch ($apiRequest) {
                case "AddDetailView":
                case "AddPurchase":
                case "AddCartAddition":
                case "AddBookmark":
                    $req = new $command(
                        $userId,
                        $itemId
                    );
                    break;
                case "AddRating":
                    $rating = $options['rating'];
                    unset($options['rating']);
                    $req = new $command(
                        $userId,
                        $itemId,
                        $rating,
                        $options
                    );
                    break;
                case "SetViewPortion":
                    $portion = $options['portion'];
                    unset($options['portion']);
                    $req = new $command(
                        $userId,
                        $itemId,
                        $portion,
                        $options
                    );

                    break;
                case "RecommendItemsToUser":
                    $limit = $options['limit'];
                    unset($options['limit']);
                    $req = new $command(
                        $userId,
                        $limit,
                        $options
                    );
                    break;
            }
            if ($req) {
                $req->setTimeout(5000);
                $requests[] = $req;
            }
            $this->segmentMapping->map($apiRequest, $userId);
        }


        //$this->logger->debug('Recombee requests:'.var_dump($batchOptions));
        $this->setCacheId($requests);
        if ($this->hasCommandOutput()) {
            return $this->getCommandOutput();
        }
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

    /**
     * @param                                                          $content
     * @param int                                                      $minAge
     * @param int                                                      $maxAge
     */
    public function hasAbandonedCart($content, $minAge, $maxAge)
    {
        $tokens = $this->recombeeTokenFinder->findTokens($content);
        if (!empty($tokens)) {
            foreach ($tokens as $key => $token) {
                $this->getAbandonedCart($token, $minAge, $maxAge);
                $items = $this->getcommandoutput();
                if (!empty($items)) {
                    return true;
                }
            }
        }
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
        if (!empty($this->getCommandOutput()['recomms'])) {
            return $this->getCommandOutput()['recomms'];
        }

        return [];
    }

    public function callApi($requests)
    {
        if (empty($requests)) {
            return;
        }

        if (!isset($requests[0])) {
            $requests = [$requests];
        }
        $this->setCacheId($requests);

        if ($this->hasCommandOutput()) {
            return $this->getCommandOutput();
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
        return $this->commandOutput[$this->getCacheId()];
    }

    /**
     * @param mixed $commandOutput
     */
    public function hasCommandOutput()
    {
        if (!empty($this->commandOutput[$this->getCacheId()])) {
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
        $this->commandOutput[$this->getCacheId()] = $commandOutput;
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
     * @return
     */
    public function getCacheId()
    {
        return $this->cacheId;
    }

    /**
     * @param  $cacheId
     */
    public function setCacheId($cacheId)
    {
        $this->cacheId = serialize($cacheId);
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

