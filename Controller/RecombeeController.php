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
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;

class RecombeeController extends AbstractStandardFormController
{

    private $sessionName = 'mautic.recombee.example';

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

        /** @var RecombeeHelper $recombeeHelper */
            //  $recombeeHelper = $this->get('mautic.recombee.helper');
            // $content = '<div><div class="mautic-recombee" data-type="RecommendItemsToItem" data-id="1" data-user-id="65" data-item-id="1"></div></div>';
            // $content = '<div>{Recombee=1|type=RecommendItemsToItem|user-id=1|item-id=1}</div>';
        //$recombeeHelper->findTokenToReplace($content);
//         $recombeeHelper->tokenReplace($content);
         $template = $this->get('twig')->createTemplate('Hello, {{ name }}');
//         preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', ' <p>{{ item.price }}
// {{ item.url }}
// {{ item.product }}</p>', $matches);
//         die(print_r($matches));
//
//         $output = $template->render(['name' => 'Bob']);
//          echo $output;
//          die();

        /** @var RecombeeHelper $recombeeHelper */
        /* $recombeeHelper = $this->get('mautic.recombee.helper');
         try {
             $response = $recombeeHelper->getClient()->send(new Reqs\AddCartAddition(2, 1000, [ //optional parameters:
                 'timestamp' => '1519250246',
                 'cascadeCreate' => true,
                 'amount' => 1,
                 'price' => 250,
             ]));
         } catch (Ex\ApiException $e) {
             echo $e->getCode();
             $response = $e->getMessage();
         }
         die(print_r($response));
         die();*/

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
        /** @var RecombeeHelper $recombeeHelper */
        $recombeeHelper = $this->get('mautic.recombee.helper');
        $viewParameters = [];
        switch ($action) {
            case 'new':
            case 'edit':
            case 'example':
                $entity = $args['entity'];

                $entity->setType('RecommendItemsToUser');
                $params = $recombeeHelper->getRecombeeKeysFromEntity($entity);
                $viewParameters['params'] = $params;
                $properties = $recombeeHelper->getClient()->send(new $params['listPropertyClass']());;

                /*         $template = $this->get('twig')->createTemplate($entity->getTemplate());
                         $items = [];
                         $items[] = ['title'=>'test title', 'description'=>'desc'];
                         $viewParameters['templateOutput'] = $template->render(['items' => $items]);*/
                $viewParameters['properties'] = $properties;


                break;
            case 'view':
            case 'example':
                $entity = $args['entity'];

                $args['viewParameters'] = array_merge(
                    $args['viewParameters'],
                    [
                        'entity' => $entity,
                        'exampleForm' => $this->generateExampleForm($entity),
                    ]
                );
                break;
        }
        $args['viewParameters'] = array_merge($args['viewParameters'], $viewParameters);

        return $args;
    }

    /**
     * Generates example form and action.
     *
     * @param   $objectId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function exampleAction($objectId)
    {
        $entity = $this->getModel($this->getModelName())->getEntity($objectId);

        return $this->generateExampleForm($entity);
    }

    /**
     * @param Recombee $entity
     */
    private function generateExampleForm(Recombee $entity)
    {
        /** RecombeeHelper $recombeeHelper  */
        $recombeeHelper = $this->get('mautic.recombee.helper');
        //do some default filtering
        $leadModel = $this->getModel('lead.lead');
        $session = $this->get('session');
        $search = $this->request->get('search', $session->get($this->sessionName, ''));
        $session->set($this->sessionName, $search);
        $items = [];
        $filter = [];
        $search = 'tuli';
        if (!empty($search)) {
            $params = $recombeeHelper->getRecombeeKeysFromEntity($entity);
            try {
                $items = $recombeeHelper->getClient()->send(new $params['listClass']([ //optional parameters:
                    'filter' => '"'.strtolower($search).'" in lower(\''.$params['search'].'\')',
                    'count' => $entity->getNumberOfItems(),
                    'offset' => 0,
                    'returnProperties' => true,
                    'includedProperties' => [$params['search']],
                ]));
            } catch (Ex\ApiException $e) {
                echo $e->getMessage();
            }
        }

        $choices = [];
        foreach ($items as $i) {
            $choices[$i[$params['key']]] = $i['name'];
        }
        $action = $this->generateUrl('mautic_recombee_action',
            ['objectAction' => 'example', 'objectId' => $entity->getId()]);
        $form = $this->get('form.factory')->create(
            'recombee_example',
            [],
            [
                'action' => $action,
                'choices' => $choices,
            ]
        );

        $postActionVars = [
            'returnUrl' => $action,
        ];

        $tmpl = $this->request->get('tmpl', 'index');
        $contentTemplate = 'MauticRecombeeBundle:Recombee:example.html.php';
        $viewParameters = [
            'tmpl' => $tmpl,
            'choices' => $choices,
            'searchValue' => $search,
            'filter' => $filter,
            'action' => $action,
            'form' => $form->createView(),
            'currentRoute' => $this->generateUrl(
                'mautic_recombee_action',
                [
                    'objectAction' => 'example',
                    'objectId' => $entity->getId(),
                ]
            ),
        ];
            return $this->delegateView(
                [
                    'viewParameters' => $viewParameters,
                    'contentTemplate' => $contentTemplate,
                ]
            );
           /* return $this->renderView(
                $contentTemplate,
                $viewParameters
            );*/
    }

}
