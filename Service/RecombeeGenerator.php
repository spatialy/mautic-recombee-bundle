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

    public function __construct(
        RecombeeModel $recombeeModel,
        RecombeeApi $recombeeApi,
        ContactTracker $contactTracker,
        LeadModel $leadModel,
        \Twig_Environment  $twig
    ) {
        $this->recombeeApi    = $recombeeApi;
        $this->recombeeModel  = $recombeeModel;
        $this->contactTracker = $contactTracker;
        $this->leadModel      = $leadModel;
        $this->twig = $twig;
    }

    public function getContentByToken(RecombeeToken $recombeeToken, $template)
    {
        $this->contactTracker->setSystemContact($this->leadModel->getEntity(1));
        $recombee = $this->recombeeModel->getEntity($recombeeToken->getRecombeeId());
        $testLead = $this->leadModel->getEntity(1);
        $recombeeToken->setDefaultFromEntity($recombee, $testLead);

        $templateContent = implode('',$recombee->getPageTemplate());
        if ('emailTemplate' === $template) {
            $templateContent = implode('', $recombee->getEmailTemplate());
        }


        $options = [
            'filter'       => $recombee->getFilter(),
            'booster'      => $recombee->getBoost(),
            'returnProperties' => true,
        ];

        try {
            switch ($recombeeToken->getRecombeeType()) {
                case "RecommendItemsToUser":
                    $items = $this->recombeeApi->getClient()->send(
                        new Reqs\RecommendItemsToUser(
                            $recombeeToken->getRecombeeUserId(),
                            $recombeeToken->getLimit(),
                            $options
                        )
                    );

                    $template = $this->twig->createTemplate($templateContent);
                    $output = '';
                    foreach ($items['recomms'] as $item) {
                   // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $templateContent , $matches);
                    $output .= $template->render($item['values']);
                    }
                    return $output;

            }
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
}

