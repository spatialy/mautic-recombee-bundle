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
use MauticPlugin\MauticExtendeeAnalyticsBundle\Helper\GoogleAnalyticsTrait;
use MauticPlugin\MauticExtendeeAnalyticsBundle\Integration\EAnalyticsIntegration;
use MauticPlugin\MauticRecombeeBundle\Integration\RecombeeIntegration;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class GoogleAnalyticsHelper
{
    use GoogleAnalyticsTrait;
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $metrics;

    private $analyticsFeatures;
    private $recombeeFeatures;

    private $recombeeEvents;

    /**
     * @var EntityManager
     */
    private $entityManager;

    private $utmTags = [];


    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Generator constructor.
     *
     * @param IntegrationHelper   $integrationHelper
     * @param TranslatorInterface $translator
     * @param EntityManager       $entityManager
     * @param FormFactory         $formFactory
     * @param RouterInterface     $router
     *
     * @internal param FormFactoryBuilder $formFactoryBuilder
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        TranslatorInterface $translator,
        EntityManager $entityManager,
        FormFactory $formFactory,
        RouterInterface $router
    ) {

        $this->integrationHelper  = $integrationHelper;
        $this->translator         = $translator;
        $this->entityManager      = $entityManager;
        $this->router             = $router;
        $this->formFactory = $formFactory;
    }


    /**
     * @return bool
     */
    public function enableRecombeeIntegration()
    {
        /** @var RecombeeIntegration $recombeeIntegration */
        $recombeeIntegration = $this->integrationHelper->getIntegrationObject('Recombee');
        if ($recombeeIntegration && $recombeeIntegration->getIntegrationSettings()->getIsPublished() && $this->enableEAnalyticsIntegration()) {
            $this->recombeeFeatures = $recombeeIntegration->getIntegrationSettings()->getFeatureSettings();
            return true;
        }

        return false;
    }

    /**
     * @param mixed $recombeeEvents
     */
    public function setRecombeeEvents($recombeeEvents)
    {
        $this->recombeeEvents = $recombeeEvents;
        foreach ($recombeeEvents as $recombeeEvent) {
            if (!empty($recombeeEvent['channel']) && !empty($recombeeEvent['channelId'])) {
                $this->getUtmTagsFromChannel($recombeeEvent['channel'], $recombeeEvent['channelId']);
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
        } elseif ($channel == 'dynamicContent') {
            $table = 'dynamic_content';
        }  elseif ($channel == 'notification') {
            $table = 'push_notifications';
        } else {
            $table = $channel;
        }

        $q->select('e.utm_tags')
            ->from(MAUTIC_TABLE_PREFIX.$table, 'e')
            ->where(
                $q->expr()->like('e.id', ':channelId')
            )
            ->setParameter('channelId', $channelId);
        $utmTags = $q->execute()->fetchColumn();

        try {
            $tags = \GuzzleHttp\json_decode($utmTags);
        } catch (\Exception $exception) {
            $tags = unserialize($utmTags);
        }

        $this->utmTags[$channel][$channelId] = $tags ;
        return $this->utmTags[$channel][$channelId];
    }

    /**
     * @return mixed
     */
    public function getRecombeeFeatures()
    {
        return $this->recombeeFeatures;
    }
}