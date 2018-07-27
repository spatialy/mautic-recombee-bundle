<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Helper;


use Doctrine\ORM\EntityManager;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Translation\TranslatorInterface;

class GoogleAnalyticsHelper
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $metrics;

    private $integrationFeatures;

    private $recombeeEvents;

    /**
     * @var EntityManager
     */
    private $entityManager;

    private $utmTags = [];

    /**
     * Generator constructor.
     *
     * @param IntegrationHelper   $integrationHelper
     * @param TranslatorInterface $translator
     * @param EntityManager       $entityManager
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        TranslatorInterface $translator,
        EntityManager $entityManager
    ) {

        $this->integrationHelper = $integrationHelper;
        $this->translator        = $translator;
        $this->entityManager     = $entityManager;
    }

    /**
     * @return bool
     */
    public function enableRecombeeIntegration()
    {
        /** @var RecombeeIntegration $recombeeIntegration */
        $recombeeIntegration = $this->integrationHelper->getIntegrationObject('Recombee');
        if ($recombeeIntegration && $recombeeIntegration->getIntegrationSettings()->getIsPublished()) {
            $this->integrationFeatures = $recombeeIntegration->getIntegrationSettings()->getFeatureSettings();
            if (empty($this->integrationFeatures['clientId']) || empty($this->integrationFeatures['viewId'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFlatUtmTags()
    {
        $flat = [];
        foreach ($this->utmTags as $fields) {
            foreach ($fields as $utmTags) {
                foreach ($utmTags as $key => $tag) {
                    if (empty($tag)) {
                        continue;
                    }
                    if (!isset($flat[$key][$tag])) {
                        $flat[$key][$tag] = $tag;
                    }
                }
            }
        }
        return $flat;
    }

    public function getFilter()
    {
        $filter = '';
        foreach ($this->getFlatUtmTags() as $key => $utmTag) {
            $filterImp = [];
            foreach ($utmTag as $tag) {
                //$filter.= 'ga:'.strtolower($key).'=='.$utmTag.';';
                $filterImp[] = 'ga:'.strtolower(str_replace('utm', '', $key)).'=='.$tag.'';
            }
            $filter .= implode(',', $filterImp).';';
        }
        $filter  = substr_replace($filter, '', -1);
        return str_replace('ga:content', 'ga:adContent', $filter);
    }


    /**
     * @return array
     */
    public function getMetricsFromConfig()
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

        if (!empty($this->integrationFeatures['goals']) && !empty($this->integrationFeatures['goals']['list'])) {
            foreach ($this->integrationFeatures['goals']['list'] as $goal) {
                $metrics['goals']['ga:goal'.$goal['value'].'Completions'] = $goal['label'];
            }
        }

        $this->metrics = $metrics;

        return $metrics;
    }

    /**
     * @return array
     */
    public function getRawMetrics()
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
     * @return mixed
     */
    public function getIntegrationFeatures()
    {
        return $this->integrationFeatures;
    }

    /**
     * @param mixed $recombeeEvents
     */
    public function setRecombeeEvents($recombeeEvents)
    {
        $this->recombeeEvents = $recombeeEvents;
        foreach ($recombeeEvents as $recombeeEvent) {
            if (!empty($recombeeEvent['channel']) && !empty($recombeeEvent['channelId'])) {
                $this->getUtmTagsFromChannel($recombeeEvent['channel'],$recombeeEvent['channelId']);
            }
        }
    }

    /**
     * @param $channel
     * @param $channelId
     *
     * @return mixed
     */
    private function getUtmTagsFromChannel($channel, $channelId)
    {
        // already exists
        if (isset($this->utmTags[$channel][$channelId])) {
            return $this->utmTags[$channel][$channelId];
        }

        $q = $this->entityManager->getConnection()->createQueryBuilder();
        if ($channel == 'email') {
            $table = 'emails';
        }else{
            $table = $channel;
        }

        $q->select('e.utm_tags')
            ->from(MAUTIC_TABLE_PREFIX.$table, 'e')
            ->where(
                $q->expr()->like('e.id', ':channelId')
            )
            ->setParameter('channelId', $channelId);

        $this->utmTags[$channel][$channelId] = unserialize($q->execute()->fetchColumn());
        return $this->utmTags[$channel][$channelId];
    }
}