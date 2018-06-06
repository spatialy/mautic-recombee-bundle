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
use Mautic\LeadBundle\Tracker\ContactTracker;
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
     * @var ContactTracker
     */
    private $contactTracker;

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

    /**
     * RecombeeGenerator constructor.
     *
     * @param RecombeeModel     $recombeeModel
     * @param RecombeeApi       $recombeeApi
     * @param ContactTracker    $contactTracker
     * @param LeadModel         $leadModel
     * @param \Twig_Environment $twig
     * @param ApiCommands       $apiCommands
     */
    public function __construct(
        RecombeeModel $recombeeModel,
        RecombeeApi $recombeeApi,
        ContactTracker $contactTracker,
        LeadModel $leadModel,
        \Twig_Environment $twig,
        ApiCommands $apiCommands
    ) {
        $this->recombeeApi    = $recombeeApi;
        $this->recombeeModel  = $recombeeModel;
        $this->contactTracker = $contactTracker;
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

        $options['filter']           = $recombee->getFilter();
        $options['booster']          = $recombee->getBoost();
        $options['returnProperties'] = true;
        $recombeeToken->setAddOptions($options);
        $recombeeToken->setUserId(288);
        try {
            switch ($recombeeToken->getType()) {
                case "RecommendItemsToUser":
                    $this->apiCommands->callCommand($recombeeToken->getType(), $recombeeToken->getOptions([ 'userId', 'limit']));
                    $items = $this->apiCommands->getCommandOutput();
                    break;
                case "ListUserCartAdditions":
                    $this->apiCommands->getAbandonedCart($recombeeToken, 0, 12);
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

        $templateContent = implode('', $recombee->getTemplate());
        $items = $this->getResultByToken($recombeeToken);

        if (!empty($items)) {
            $template = $this->twig->createTemplate($templateContent);
            $output   = '';
            foreach ($items as $item) {
                // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $templateContent , $matches);
                $output .= $template->render($item['values']);
            }

            return $output;
        }
    }
}

