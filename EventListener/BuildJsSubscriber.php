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
use Mautic\PluginBundle\Helper\IntegrationHelper;

/**
 * Class BuildJsSubscriber.
 */
class BuildJsSubscriber extends CommonSubscriber
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * BuildJsSubscriber constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {

        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MAUTIC_JS => [
                ['onBuildJsTop', 900],
            ],
        ];
    }

    /**
     * @param BuildJsEvent $event
     */
    public function onBuildJsTop(BuildJsEvent $event)
    {
        /** @var RecombeeIntegration $recombeeIntegration */
        $recombeeIntegration = $this->integrationHelper->getIntegrationObject('Recombee');
        if (!$recombeeIntegration || !$recombeeIntegration->getIntegrationSettings()->getIsPublished()) {
            return;
        }
        $features = $recombeeIntegration->getIntegrationSettings()->getFeatureSettings();
        if (empty($features['mapDetailView']) || empty($features['mapDetailView_param'])) {
            return;
        }


        //basic js
        $js = <<<JS
        function mergeObjects(){var tmpObj={};for(var o in arguments){for(var m in arguments[o]){tmpObj[m]=arguments[o][m];}}
return tmpObj;}
function getQuery(q) {
   return (window.location.search.match(new RegExp('[?&]' + q + '=([^&]+)')) || [, null])[1];
}
var detailViewParam =   getQuery('{$features['mapDetailView_param']}');

if(detailViewParam != null){
 MauticJS.getInput2 = function(task, type) {
        var matches = [];
        if (typeof MauticJS.inputQueue !== 'undefined' && MauticJS.inputQueue.length) {
            for (var i in MauticJS.inputQueue) {
                if (MauticJS.inputQueue[i][0] === task && MauticJS.inputQueue[i][1] === type) {
                    var parms = MauticJS.inputQueue[i][2];
                          if (typeof parms === 'undefined') {
            var  parms = { Recombee: '{"AddDetailView":{"itemId":'+detailViewParam+'}}' };
       }else{
              parms = mergeObjects(parms, { Recombee: '{"AddDetailView":{"itemId":'+detailViewParam+'}}' });
        }   
                    MauticJS.inputQueue[i][2] = parms;
                    matches.push(MauticJS.inputQueue[i]);
                }
            }
        }
        return matches; 
    }
   }
JS;
        $event->appendJs($js, 'CustomRecombee');
    }


}
