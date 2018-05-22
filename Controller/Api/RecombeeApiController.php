<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class RecombeeApiController.
 */
class RecombeeApiController extends CommonApiController
{
    /**
     * @var RecombeeHelper
     */
    protected $recombeeHelper;

    private $components = ['CartAddition', 'Purchase', 'Rating', 'Bookmark', 'DetailView'];

    private $actions = ['Add', 'Delete'];

    /**
     * @param FilterControllerEvent $event
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->leadModel      = $this->getModel('lead.lead');
        $this->recombeeHelper = $this->container->get('mautic.recombee.helper');
        parent::initialize($event);
    }


    /**
     * @param $component
     *
     * @return array|Response
     */
    public function processAction($component)
    {
        $data = $this->request->request->all();
        /** @var ApiCommands $apiCommands */
        $apiCommands = $this->get('mautic.recombee.service.api.commands');
        $apiCommands->callCommand($component, $this->request->request->all());
        if ($apiCommands->getCommandResult()) {
            $view     = $this->view(['succes' => true]);
            return $this->handleView($view);
        }

        return $this->returnError(
            $this->translator->trans(
                'mautic.plugin.recombee.api.component.error',
                ['%componet' => $component],
                'validators'
            ),
            Response::HTTP_BAD_REQUEST
        );
    }
}
