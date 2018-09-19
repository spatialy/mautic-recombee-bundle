<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Controller;

use Mautic\CoreBundle\Exception as MauticException;
use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Mautic\PageBundle\Event\PageDisplayEvent;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RecombeeController extends AbstractStandardFormController
{

    /**
     * {@inheritdoc}
     */
    protected function getJsLoadMethodPrefix()
    {
        return 'recombee';
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return 'recombee.recombee';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteBase()
    {
        return 'recombee';
    }

    /***
     * @param null $objectId
     *
     * @return string
     */
    protected function getSessionBase($objectId = null)
    {
        return 'recombee'.(($objectId) ? '.'.$objectId : '');
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'MauticRecombeeBundle:Recombee';
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        return $this->batchDeleteStandard();
    }

    /**
     * @param $objectId
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cloneAction($objectId)
    {
        return $this->cloneStandard($objectId);
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction($objectId, $ignorePost = false)
    {
        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * @param int $page
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction($page = 1)
    {
        return $this->indexStandard($page);
    }

    /**
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newAction()
    {
        return $this->newStandard();
    }

    /**
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        //set the page we came from
        $page = $this->get('session')->get('mautic.recombee.page', 1);
        $returnUrl = $this->generateUrl('mautic_recombee_index', ['page' => $page]);

        return $this->postActionRedirect(
            [
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'MauticRecombeeBundle:Recombee:index',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_recombee_index',
                    'mauticContent' => 'recombee',
                ],
            ]
        );
    }

    /**
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function deleteAction($objectId)
    {
        return $this->deleteStandard($objectId);
    }

    /**
     * @param $args
     * @param $action
     *
     * @return mixed
     */
    protected function getViewArguments(array $args, $action)
    {
        /** @var ApiCommands $apiCommands */
        $apiCommands    = $this->get('mautic.recombee.service.api.commands');
        $integration    = $this->get('mautic.integration.recombee');
        $viewParameters = [];
        switch ($action) {
            case 'new':
            case 'edit':
                $viewParameters['properties'] = $apiCommands->callCommand('ListItemProperties');
                $viewParameters['settings']   = $integration->getIntegrationSettings()->getFeatureSettings();
                break;
        }
        $args['viewParameters'] = array_merge($args['viewParameters'], $viewParameters);

        return $args;
    }

    /**
     * @return JsonResponse
     */
    public function processAction()
    {
        if (!$this->get('mautic.security')->isAnonymous()) {
            return new JsonResponse(
                [
                    'success' => 0,
                ]
            );
        }
        /** @var ApiCommands $apiCommands */
        $apiCommands = $this->get('mautic.recombee.service.api.commands');
        /** @var LeadModel $leadModel */
        $leadModel = $this->get('mautic.lead.model.lead');
        $lead      = $leadModel->getCurrentLead();


        /** @var ContactTracker $contactTracker */
        //$contactTracker = $this->get('mautic.tracker.contact');
        $options           = $this->request->request->all();

        $recombee = $this->request->get('recombee');
        $requests = json_decode(base64_decode($recombee), true);
        $response = [];
        foreach ($requests as $request) {
            $request = json_decode($request, true);
            if (!is_array($request) || !isset($request['component'])) {
                continue;
            }
            $component = $request['component'];
            $request['userId'] = $lead->getId();
            unset($request['component']);
            $apiCommands->callCommand($component, $request);
            $response[] = $apiCommands->getCommandOutput();
        }
        return new JsonResponse(
            [
                'response' => $response,
            ]
        );
    }
}
