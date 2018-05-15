<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\BuildJsEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Templating\Helper\AssetsHelper;
use Mautic\FormBundle\Model\FormModel;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeAttr;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class BuildJsSubscriber.
 */
class BuildJsSubscriber extends CommonSubscriber
{

    /**
     * BuildJsSubscriber constructor.
     *
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MAUTIC_JS => ['onBuildJs', 300],
        ];
    }

    /**
     * Adds the MauticJS definition and core
     * JS functions for use in Bundles. This
     * must retain top priority of 1000.
     *
     * @param BuildJsEvent $event
     */
    public function onBuildJs(BuildJsEvent $event)
    {
        $recombeeUrl = $this->router->generate(
            'mautic_recombee_api_content', [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $recombeeAttr = (new RecombeeAttr())->getRecombeeAttr();
        $cond = '';
        foreach ($recombeeAttr as $attr) {
            $cond.='
                if(\'undefined\' !== typeof node.dataset[\''.$attr.'\']){
                  params[\''.$attr.'\'] = node.dataset[\''.$attr.'\'];
                }';
        }

        $js = <<<JS
        
MauticJS.replaceRecombeeContent = function (params) {
    params = {};
    var recombeeContentSlot = document.querySelectorAll('.mautic-recombee');
    if (recombeeContentSlot.length) {
        MauticJS.iterateCollection(recombeeContentSlot)(function(node, i) {
            
            var id = node.dataset['id'];
            if ('undefined' === typeof id) {
                node.innerHTML = '';
                return;
            }
            
            params['id'] = id;
            
            {$cond}
            
            var url = '{$recombeeUrl}'.replace('recombeeIdlaceholder', id);

            MauticJS.makeCORSRequest('GET', url, params, function(response, xhr) {
                if (response.content) {
                    var recombeeContent = response.content;
                    node.innerHTML = recombeeContent;

                    if (response.id && response.sid) {
                        MauticJS.setTrackedContact(response);
                    }

                }
            });
        });
    }
};

MauticJS.beforeFirstEventDelivery(MauticJS.replaceRecombeeContent);
JS;
        $event->appendJs($js, 'Mautic Recombee Content');
    }
}
