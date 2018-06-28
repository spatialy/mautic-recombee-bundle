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

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Response;

class JsController extends CommonController
{
    /**
     * @param $focusId
     *
     * @return Response
     */
    public function generateAction($focusId)
    {
        $js = <<<JS
    var contentEl = document.querySelector('.woocommerce-mini-cart');
      var injectEl = document.querySelector('#recombee-focus-{$focusId}');
      console.log(contentEl);
      console.log(injectEl);
        if(contentEl && injectEl)
        {
            injectEl.innerHTML = contentEl.parentElement.innerHTML
        }
JS;

        return new Response(
            $js,
            200,
            [
                'Content-Type'           => 'application/javascript',
            ]
        );
    }
}