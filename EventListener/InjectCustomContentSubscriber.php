<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\EventListener;

use Mautic\CampaignBundle\Entity\Event;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecombeeBundle\Helper\GoogleAnalyticsHelper;
use MauticPlugin\MauticRecombeeBundle\Integration\RecombeeIntegration;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class InjectCustomContentSubscriber extends CommonSubscriber
{

    /**
     * @var GoogleAnalyticsHelper
     */
    private $analyticsHelper;

    /**
     * InjectCustomContentSubscriber constructor.
     *
     * @param GoogleAnalyticsHelper $analyticsHelper
     */
    public function __construct(GoogleAnalyticsHelper $analyticsHelper)
    {

        $this->analyticsHelper = $analyticsHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['injectViewCustomContent', 0],
        ];
    }

    /**
     * @param CustomContentEvent $customContentEvent
     */
    public function injectViewCustomContent(CustomContentEvent $customContentEvent)
    {
        if (!$this->analyticsHelper->enableRecombeeIntegration() || $customContentEvent->getContext() != 'details.stats.graph.below' || $customContentEvent->getViewName()!= 'MauticCampaignBundle:Campaign:details.html.php'
        ) {
            return;
        }

        //events from table by start/last
        $parameters = $customContentEvent->getVars();
        $campaignEvents = $parameters['campaignEvents'];
        /** @var Event $campaignEvent */
        $recombeeEvents = [];
        foreach ($campaignEvents as $campaignEvent) {
            if (strpos($campaignEvent['type'], 'recombee') === 0) {
                $recombeeEvents[] = $campaignEvent;
            }
        }
        // No Recombee events for this campaign
        if (empty($recombeeEvents)) {
            return;
        }

        foreach ($recombeeEvents as $recombeeEvent) {

        }
        $this->analyticsHelper->setRecombeeEvents($recombeeEvents);

        $dateFrom = '';
        $dateTo = '';
        if (!empty($parameters['dateRangeForm'])) {
            /** @var FormView $dateRangeForm */
            $dateRangeForm = $parameters['dateRangeForm'];
            $dateFrom = $dateRangeForm->children['date_from']->vars['data'];
            $dateTo = $dateRangeForm->children['date_to']->vars['data'];
        }

        $customContentEvent->addTemplate('MauticRecombeeBundle:Analytics:analytics-details.html.php',
            [
                'tags'   => $this->analyticsHelper->getFlatUtmTags(),
                'keys'       => $this->analyticsHelper->getIntegrationFeatures(),
                'filters'    => $this->analyticsHelper->getFilter(),
                'metrics'    => $this->analyticsHelper->getMetricsFromConfig(),
                'rawMetrics' => $this->analyticsHelper->getRawMetrics(),
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);

    }


}
