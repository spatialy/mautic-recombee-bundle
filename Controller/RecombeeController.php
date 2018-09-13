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
use Mautic\PageBundle\Event\PageDisplayEvent;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
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
        if (empty($objectId)) {
            return $this->newStandard();
        }else{
            return $this->editAction($objectId);
        }
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
        $apiCommands = $this->get('mautic.recombee.service.api.commands');
        $integration = $this->get('mautic.integration.recombee');
        $viewParameters = [];
        switch ($action) {
            case 'new':
            case 'edit':
              $viewParameters['properties'] = $apiCommands->callCommand('ListItemProperties');
              $viewParameters['settings'] =   $integration->getIntegrationSettings()->getFeatureSettings();
            break;
        }
        $args['viewParameters'] = array_merge($args['viewParameters'], $viewParameters);
        return $args;
    }

    public function templateAction($id)
    {



    }

    /**
     * @param $id
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function generateAction($id)
    {
        // Don't store a visitor with this request
        defined('MAUTIC_NON_TRACKABLE_REQUEST') || define('MAUTIC_NON_TRACKABLE_REQUEST', 1);

        /** @var \MauticPlugin\MauticFocusBundle\Model\FocusModel $model */
        $model = $this->getModel('focus');
        $focus = $model->getEntity($id);

        if ($focus) {
            if (!$focus->isPublished()) {
                return new Response('', 200, ['Content-Type' => 'application/javascript']);
            }

            $content  = $model->generateJavascript($focus, false, true);
            $response = new Response($content, 200, ['Content-Type' => 'application/javascript']);

            return $response;
        } else {
            return new Response('', 200, ['Content-Type' => 'application/javascript']);
        }
    }

}
