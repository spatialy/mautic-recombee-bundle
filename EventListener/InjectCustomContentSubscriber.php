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
use MauticPlugin\MauticRecombeeBundle\Integration\RecombeeIntegration;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class InjectCustomContentSubscriber extends CommonSubscriber
{
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var TemplatingHelper
     */
    protected $templatingHelper;

    /** @var Translator */
    protected $translator;

    /** @var array */
    private $metrics = [];

    /**
     * ButtonSubscriber constructor.
     *
     * @param IntegrationHelper              $integrationHelper
     * @param TemplatingHelper               $templateHelper
     * @param Translator|TranslatorInterface $translator
     * @param RouterInterface                $router
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        TemplatingHelper $templateHelper,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->templateHelper    = $templateHelper;
        $this->translator        = $translator;
        $this->router            = $router;
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
                /** @var RecombeeIntegration $recombeeIntegration */
        $recombeeIntegration = $this->integrationHelper->getIntegrationObject('Recombee');
        if (!$recombeeIntegration || !$recombeeIntegration->getIntegrationSettings()->getIsPublished() || $customContentEvent->getContext() != 'details.stats.graph.below' || $customContentEvent->getViewName()!= 'MauticCampaignBundle:Campaign:details.html.php'
        ) {
            return;
        }

        $keys = $recombeeIntegration->getIntegrationSettings()->getFeatureSettings();
        if (empty($keys['clientId']) || empty($keys['viewId'])) {
            return;
        }

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
       //     return;
        }


        $filters = '';
        $tags = [];
        $utmTags = ['source'=>'madesimple.cloud'];
        foreach ($utmTags as $tagKey=>$utmTag) {
            $tags[$tagKey] = $utmTag;
            $filters.= 'ga:'.strtolower($tagKey).'=='.$utmTag.';';
        }

        $dateFrom = '';
        $dateTo = '';
        if (!empty($parameters['dateRangeForm'])) {
            /** @var FormView $dateRangeForm */
            $dateRangeForm = $parameters['dateRangeForm'];
            $dateFrom = $dateRangeForm->children['date_from']->vars['data'];
            $dateTo = $dateRangeForm->children['date_to']->vars['data'];
        }

        // Remove last line
        $filters  = substr_replace($filters, '', -1);
        $filters = str_replace('ga:content', 'ga:adContent', $filters);
        $content = $this->templateHelper->getTemplating()->render(
            'MauticRecombeeBundle:Analytics:analytics-details.html.php',
            [
                'tags'   => $tags,
                'keys'       => $keys,
                'filters'    => $filters,
                'metrics'    => $this->getMetricsFromConfig($keys),
                'rawMetrics' => $this->getRawMetrics(),
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

            ]
        );

        $customContentEvent->addContent($content);

    }

    private function getRawMetrics()
    {
        $rawMetrics = [];
        foreach ($this->metrics as $metrics) {
            foreach ($metrics as $metric => $label) {
                $rawMetrics[$metric] = $label;
            }
        }

        return $rawMetrics;
    }


    /**
     * @param $keys
     */
    private function getMetricsFromConfig($keys)
    {
        if (!empty($this->metrics)) {
            return $this->metrics;
        }
        $metrics = [
            'overview' => [
                'ga:sessions'           => $this->translator->trans('plugin.extendee.analytics.sessions'),
                'ga:avgSessionDuration' => $this->translator->trans('plugin.extendee.analytics.average.duration'),
                'ga:bounceRate'         => $this->translator->trans('plugin.extendee.analytics.bounce.rate'),
            ],
        ];

        if (!empty($keys['ecommerce'])) {
            $metrics['ecommerce']['ga:transactions']       = $this->translator->trans(
                'plugin.extendee.analytics.transactions'
            );
            $metrics['ecommerce']['ga:transactionRevenue'] = $this->translator->trans(
                'plugin.extendee.analytics.transactions.revenue'
            );

            $metrics['ecommerce']['ga:revenuePerTransaction'] = $this->translator->trans(
                'plugin.extendee.analytics.revenue.per.transaction'
            );
        }
        if (!empty($keys['goals']) && !empty($keys['goals']['list'])) {
            foreach ($keys['goals']['list'] as $goal) {
                $metrics['goals']['ga:goal'.$goal['value'].'Completions'] = $goal['label'];
            }
        }
        $this->metrics = $metrics;

        return $metrics;
    }

}
