<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Service;

use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticRecombeeBundle\Api\RecombeeApi;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Exceptions as Ex;
use Recombee\RecommApi\Requests as Reqs;

class RecombeeGenerator
{
    /** @var RecombeeApi */
    private $recombeeApi;

    /**
     * @var RecombeeModel
     */
    private $recombeeModel;
    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var \Twig_Extension
     */
    private $twig;

    /**
     * @var ApiCommands
     */
    private $apiCommands;

    private $header;
    private $footer;

    /**
     * RecombeeGenerator constructor.
     *
     * @param RecombeeModel     $recombeeModel
     * @param RecombeeApi       $recombeeApi
     * @param LeadModel         $leadModel
     * @param \Twig_Environment $twig
     * @param ApiCommands       $apiCommands
     */
    public function __construct(
        RecombeeModel $recombeeModel,
        RecombeeApi $recombeeApi,
        LeadModel $leadModel,
        \Twig_Environment $twig,
        ApiCommands $apiCommands
    ) {
        $this->recombeeApi    = $recombeeApi;
        $this->recombeeModel  = $recombeeModel;
        $this->leadModel      = $leadModel;
        $this->twig           = $twig;
        $this->apiCommands = $apiCommands;
    }

    /**
     * @param RecombeeToken $recombeeToken
     * @param array         $options
     */
    public function getResultByToken(RecombeeToken $recombeeToken, $options = [])
    {
        $recombee = $this->recombeeModel->getEntity($recombeeToken->getId());

        if (!$recombee instanceof Recombee) {
            return;
        }

        //$options['filter']           = $recombee->getFilter();
        //$options['booster']          = $recombee->getBoost();
        $options['returnProperties'] = true;
        $recombeeToken->setAddOptions($options);
        try {
            switch ($recombeeToken->getType()) {
                case "RecommendItemsToUser":
                $this->apiCommands->callCommand('RecommendItemsToUser', $recombeeToken->getOptions([ 'userId', 'limit']));
                    $items = $this->apiCommands->getCommandOutput();
                    break;
            }
            return $items['recomms'];

        } catch (Ex\ApiTimeoutException $e) {
            die(print_r($e->getMessage()));
            //Handle timeout => use fallback
        } catch (Ex\ResponseException $e) {
            die(print_r($e->getMessage()));
            //Handle errorneous request => use fallback
        } catch (Ex\ApiException $e) {
            die(print_r($e->getMessage()));
            //ApiException is parent of both ResponseException and ApiTimeoutException
        }
    }


    public function getContentByToken(RecombeeToken $recombeeToken)
    {
        $recombee = $this->recombeeModel->getEntity($recombeeToken->getId());

        if (!$recombee instanceof Recombee) {
            return;
        }

        $templateContent = $recombee->getTemplate()['body'];
        $items = $this->getResultByToken($recombeeToken);

        if (!empty($items)) {
            $template = $this->twig->createTemplate($templateContent);
            $output   = '';
            $tokens = [];
            $tokens['keys'] = implode(',', array_column($items, 'id'));
            foreach ($items as $item) {
                $item['values'] = array_merge($item['values'], $tokens);
                // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $templateContent , $matches);
                $output .= $template->render($item['values']);
            }
            $headerTemplate = $this->twig->createTemplate($recombee->getTemplate()['header']);
            $footerTemplate = $this->twig->createTemplate($recombee->getTemplate()['footer']);
            return $headerTemplate->render($tokens).$output.$footerTemplate->render($tokens);
        }
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->footer;
    }
}

