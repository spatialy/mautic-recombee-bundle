<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Controller;

use Guzzle\Http\Message\Response;
use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeGenerator;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;

class TestsController extends CommonAjaxController
{


    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return 'recombee.recombee';
    }

    public function runAction()
    {
        /** @var LeadModel $leadModel */
        $leadModel = $this->getModel('lead');
        /** @var DynamicContentHelper $helper */
        $helper = $this->get('mautic.helper.dynamicContent');
        /** @var DeviceTrackingServiceInterface $deviceTrackingService */
        $deviceTrackingService = $this->get('mautic.lead.service.device_tracking_service');
        /** @var PageModel $pageModel */
        $pageModel = $this->getModel('page');
        $leadEmail = 'rafoxesi@loketa.com';
        $firstname = 'Testname';
        $lastname  = 'Testlastname';

        $leadFields              = [];
        $leadFields['email']     = $leadEmail;
        $leadFields['firstname'] = $firstname;
        $leadFields['lastname']  = $lastname;
        $lead                    = $leadModel->checkForDuplicateContact($leadFields);


        /** @var ApiCommands $apiCommand */
        $apiCommand = $this->get('mautic.recombee.service.api.commands');
        // import item
        $apiCommand->ImportItems($this->getItems()[0]);
        if ($apiCommand->getCommandOutput() != "ok") {
           throw new \Exception('Import item failed ');
        }
        // import items
        $apiCommand->ImportItems($this->getItems());
        if (!is_array($apiCommand->getCommandOutput())) {
            throw new \Exception('Import items failed '.print_r($apiCommand->getCommandOutput()));
        }

        foreach ($apiCommand->getCommandOutput() as $output) {
            if ($output['code'] != 200) {
                throw new \Exception(print_r($output, true));
            }
        }


        $apiOptions           = [];
        $apiOptions['userId'] = $lead->getId();
        $apiOptions['itemId'] = 1;
        $apiCommand->callCommand('AddDetailView', $apiOptions);
        echo $apiCommand->getCommandOutput();


        die();
    }

    private function getItems()
    {
        $items            = [];
        $items[0]['id']   = 1;
        $items[0]['namen'] = 'Test product';
        $items[0]['url']  = 'http://recombee.com';
        $items[1]['id']   = 2;
        $items[1]['namen'] = 'Test product 2';
        $items[1]['url']  = 'http://recombee.com';

        return $items;
    }
}
