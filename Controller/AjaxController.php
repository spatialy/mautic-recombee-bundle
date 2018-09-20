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
use Mautic\CoreBundle\Helper\InputHelper;
use MauticPlugin\MauticFocusBundle\Model\FocusModel;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AjaxController extends CommonAjaxController
{

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function generatePreviewAction(Request $request)
    {
        $data  = [];
        $recombee = $request->request->all();

        if (isset($recombee['recombee'])) {
            $recombeeEntity = new Recombee();
            $accessor = PropertyAccess::createPropertyAccessor();
            $recombeeArrays = InputHelper::_($recombee['recombee']);
            foreach ($recombeeArrays as $key=>$recombeeArray) {
             //   $accessor->setValue($recombeeEntity, $key, $recombeeArray);
                $setter = 'set'.ucfirst($key);
                if (method_exists($recombeeEntity, $setter)) {
                    $recombeeEntity->$setter($recombeeArray);
                }
            }
            $data['content'] = $this->get('mautic.helper.templating')->getTemplating()->render(
                'MauticRecombeeBundle:Builder\Page:generator.html.php',
                [
                    'recombee'  => $recombeeEntity,
                    'settings'  => $this->get('mautic.helper.integration')->getIntegrationObject('Recombee')->getIntegrationSettings()->getFeatureSettings(),
                    'preview' => true,
                ]
            );
        }

        return $this->sendJsonResponse($data);
    }
}
