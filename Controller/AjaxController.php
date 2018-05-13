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

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Symfony\Component\HttpFoundation\Request;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;

class AjaxController extends CommonAjaxController
{

    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return 'recombee.recombee';
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function generateExampleAction(Request $request)
    {
        /** @var RecombeeHelper $recombeeHelper */
        $recombeeHelper = $this->get('mautic.recombee.helper');
        /** @var RecombeeModel $recombeeModel */
        $recombeeModel = $this->getModel($this->getModelName());
        $recombeeId = $request->request->get('recombeeId');
        $recombeeId = 1;
        $enity = $recombeeModel->getEntity($recombeeId);

        $data = [];
        $success = false;
        $error = '';
        $content = '';
        $items = [];

        if($enity && $enity->isPublished(true)){
            $class = $enity->getRecommendationsType();
            try {
                $items =     $recombeeHelper->getClient()->send(new $class(2, 9,    [
                    'returnProperties' => true,
                    'includedProperties' => ['name']
                ] ));
                $success = true;
            } catch (Ex\ApiException $e) {
                $error =  $e->getMessage();
            }
        }



        $data['items'] = $items;
        $data['error'] = $error;
        $data['success'] = $success;

        return $this->sendJsonResponse($data);
    }
}
