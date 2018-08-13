<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\DynamicContentBundle\DynamicContentEvents;
use Mautic\DynamicContentBundle\Entity\DynamicContent;
use Mautic\DynamicContentBundle\Model\DynamicContentModel;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Entity\DoNotContact as DNC;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\NotificationBundle\Form\Type\MobileNotificationSendType;
use Mautic\NotificationBundle\Form\Type\NotificationSendType;
use Mautic\PageBundle\Helper\TrackingHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticFocusBundle\Entity\Focus;
use MauticPlugin\MauticFocusBundle\Model\FocusModel;
use MauticPlugin\MauticRecombeeBundle\EventListener\Service\CampaignLeadDetails;
use MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeDynamicContentRemoveType;
use MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeDynamicContentType;
use MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeEmailSendType;
use MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeFocusType;
use MauticPlugin\MauticRecombeeBundle\RecombeeEvents;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var EmailModel
     */
    protected $emailModel;


    /**
     * @var SendEmailToUser
     */
    private $sendEmailToUser;

    /**
     * @var RecombeeTokenReplacer
     */
    private $recombeeTokenReplacer;

    /**
     * @var CampaignLeadDetails
     */
    private $campaignLeadDetails;

    /**
     * @var TrackingHelper
     */
    private $trackingHelper;

    /**
     * @var FocusModel
     */
    private $focusModel;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var DynamicContentModel
     */
    private $dynamicContentModel;

    /**
     * @var EntityManager
     */
    private $entityManager;


    /**
     * @param LeadModel             $leadModel
     * @param EmailModel            $emailModel
     * @param EventModel            $eventModel
     * @param SendEmailToUser       $sendEmailToUser
     * @param RecombeeTokenReplacer $recombeeTokenReplacer
     * @param CampaignLeadDetails   $campaignLeadDetails
     * @param TrackingHelper        $trackingHelper
     * @param FocusModel            $focusModel
     * @param Session               $session
     * @param IntegrationHelper     $integrationHelper
     * @param DynamicContentModel   $dynamicContentModel
     * @param EntityManager         $entityManager
     *
     * @internal param MessageQueueModel $messageQueueModel
     */
    public function __construct(
        LeadModel $leadModel,
        EmailModel $emailModel,
        EventModel $eventModel,
        SendEmailToUser $sendEmailToUser,
        RecombeeTokenReplacer $recombeeTokenReplacer,
        CampaignLeadDetails $campaignLeadDetails,
        TrackingHelper $trackingHelper,
        FocusModel $focusModel,
        Session $session,
        IntegrationHelper $integrationHelper,
        DynamicContentModel $dynamicContentModel,
        EntityManager $entityManager
    ) {
        $this->leadModel             = $leadModel;
        $this->emailModel            = $emailModel;
        $this->campaignEventModel    = $eventModel;
        $this->sendEmailToUser       = $sendEmailToUser;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
        $this->campaignLeadDetails   = $campaignLeadDetails;
        $this->trackingHelper        = $trackingHelper;
        $this->focusModel            = $focusModel;
        $this->session               = $session;
        $this->integrationHelper     = $integrationHelper;

        $this->dynamicContentModel = $dynamicContentModel;
        $this->entityManager       = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD             => ['onCampaignBuild', 0],
            RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION    => [
                ['onCampaignTriggerActionSendRecombeeEmail', 0],
              //  ['onCampaignTriggerActionSendNotification', 2],
                ['onCampaignTriggerActionDynamiContent', 3],
                ['onCampaignTriggerActionDynamiContentRemove', 4],
            ],
            RecombeeEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
            RecombeeEvents::ON_CAMPAIGN_TRIGGER_DECISION => ['onCampaignTriggerDecisionInjectRecombeeFocus', 0],

        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
           $event->addDecision(
                 'recombee.focus',
                 [
                     'label'                  => 'mautic.recombee.focus.campaign.event.send',
                     'description'            => 'mautic.recombee.focus.campaign.event.send.desc',
                     'eventName'              => RecombeeEvents::ON_CAMPAIGN_TRIGGER_DECISION,
                     'formType'               => RecombeeFocusType::class,
                     'formTypeOptions'        => [
                         'update_select' => 'campaignevent_properties_focus',
                         'urls'          => true,
                     ],
                     'channel'         => 'focus',
                     'channelIdField'  => 'focus',
                 ]
             );



        $event->addAction(
            'recombee.email.send',
            [
                'label'           => 'mautic.recombee.email.campaign.event.send',
                'description'     => 'mautic.recombee.email.campaign.event.send.desc',
                'eventName'       => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => RecombeeEmailSendType::class,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_email'],
                'channel'         => 'email',
                'channelIdField'  => 'email',
            ]
        );

     /*   $event->addAction(
            'recombee.focus',
            [
                'label'                  => 'mautic.recombee.focus.campaign.event.send',
                'description'            => 'mautic.recombee.focus.campaign.event.send.desc',
                'eventName'              => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'               => RecombeeFocusType::class,
                'formTypeOptions'        => ['update_select' => 'campaignevent_properties_focus'],
                'channel'         => 'focus',
                'channelIdField'  => 'focus',
                'connectionRestrictions' => [
                    'anchor' => [
                        'decision.inaction',
                    ],
                    'source' => [
                        'decision' => [
                            'page.pagehit',
                        ],
                    ],
                ],
            ]
        );*/

        $event->addAction(
            'recombee.dynamic.content',
            [
                'label'           => 'mautic.recombee.dynamic.content.campaign.event',
                'description'     => 'mautic.recombee.dynamic.content.campaign.event.desc',
                'eventName'       => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => RecombeeDynamicContentType::class,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_dynamicContent'],
                'channel'         => 'dynamicContent',
                'channelIdField'  => 'dynamic_content',
            ]
        );


        $event->addAction(
            'recombee.dynamic.content.remove',
            [
                'label'           => 'mautic.recombee.dynamic.content.remove.campaign.event',
                'eventName'       => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => RecombeeDynamicContentRemoveType::class,
            ]
        );

        /*
         * Notification postpone at the moment
         *
         *
         */
        /*$integration = $this->integrationHelper->getIntegrationObject('OneSignal');
        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {

            $features = $integration->getSupportedFeatures();

            if (in_array('mobile', $features)) {
                $event->addAction(
                    'recombee.send_mobile_notification',
                    [
                        'label'            => 'mautic.recombee.notification.mobile.campaign.event.send',
                        'description'      => 'mautic.recombee.notification.mobile.campaign.event.send.tooltip',
                        'eventName'        => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                        'formType'         => MobileNotificationSendType::class,
                        'formTypeOptions'  => ['update_select' => 'campaignevent_properties_notification'],
                        'formTheme'        => 'MauticNotificationBundle:FormTheme\NotificationSendList',
                        'timelineTemplate' => 'MauticNotificationBundle:SubscribedEvents\Timeline:index.html.php',
                    ]
                );
            }

            $event->addAction(
                'recombee.send_notification',
                [
                    'label'            => 'mautic.recombee.notification.campaign.event.send',
                    'description'      => 'mautic.recombee.notification.campaign.event.send',
                    'eventName'        => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                    'formType'         => NotificationSendType::class,
                    'formTypeOptions'  => ['update_select' => 'campaignevent_properties_notification'],
                    'formTheme'        => 'MauticNotificationBundle:FormTheme\NotificationSendList',
                    'timelineTemplate' => 'MauticNotificationBundle:SubscribedEvents\Timeline:index.html.php',
                ]
            );
        }*/
    }

    /**
     * Triggers the action which sends email to contact.
     *
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent|null
     */
    public function onCampaignTriggerActionSendRecombeeEmail(CampaignExecutionEvent $event)
    {

        if (!$event->checkContext('recombee.email.send')) {
            return;
        }

        $leadCredentials = $event->getLeadFields();

        if (empty($leadCredentials['email'])) {
            return $event->setFailed('Contact does not have an email');
        }

        $config     = $event->getConfig();
        $emailId    = $config['email']['email'];
        $email      = $this->emailModel->getEntity($emailId);
        $campaignId = $event->getEvent()['campaign']['id'];
        $leadId     = $event->getLead()->getId();

        if (!$email || !$email->isPublished()) {
            return $event->setFailed('Email not found or published');
        }
        $options = [
            'source'        => ['campaign.event', $event->getEvent()['id']],
            'return_errors' => true,
            'dnc_as_error'  => true,
        ];

        $event->setChannel('recombee-email', $emailId);
        $email->setCustomHtml(
            $this->recombeeTokenReplacer->replaceTokensFromContent(
                $email->getCustomHtml(),
                $this->getOptionsBasedOnRecommendationsType($config['type'], $campaignId, $leadId)
            )
        );
        // check if cart has some items
        if (!$this->recombeeTokenReplacer->hasItems()) {
            return $event->setFailed(
                'No recombee results for this contact #'.$leadCredentials['id'].' and  email #'.$email->getId()
            );
        }

        $result = $this->emailModel->sendEmail($email, $leadCredentials, $options);
        if (is_array($result)) {
            $errors = implode('<br />', $result);

            // Add to the metadata of the failed event
            $result = [
                'result' => false,
                'errors' => $errors,
            ];
        } elseif (true !== $result) {
            $result = [
                'result' => false,
                'errors' => $result,
            ];
        } else {
            $result = [
                'id' => $email->getId(),
            ];
        }

        return $event->setResult($result);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerDecisionInjectRecombeeFocus(CampaignExecutionEvent $event)
    {

        if (!$event->checkContext('recombee.focus.insert')) {
            return;
        }

        $focusId = (int) $event->getConfig()['focus']['focus'];
        if (!$focusId) {
            return $event->setFailed('Focus ID #'.$focusId.' doesn\'t exist.');
        }

        /** @var Focus $focus */
        $focus = $this->focusModel->getEntity($focusId);

        // Stop If Focus not exist or not published
        if (!$focus || !$focus->isPublished()) {
            return $event->setFailed('Focus ID #'.$focusId.' doesn\'t exist or is not  published.');

        }

        $eventDetails = $event->getEventDetails();
        // STOP sent campaignEventModel just if Focus Item is opened
        if (!empty($eventDetails['hit'])) {
            $hit = $eventDetails['hit'];
            // Limit to URLS
            if (!empty($eventConfig['urls']['list'])) {
                $limitToUrl = $eventConfig['urls']['list'];
                $isUrl      = false;
                foreach ($limitToUrl as $url) {
                    if (preg_match('/'.preg_quote($url, '/').'/i', $hit->getUrl())) {
                        $isUrl = true;
                    }
                }
                // page hit url doesn't match
                if (!$isUrl) {
                    return $event->setResult(false);
                }
            }
        }

        $campaignId = $event->getEvent()['campaign']['id'];
        $leadId     = $event->getLead()->getId();


        $event->setChannel('recombee-focus', $focusId);
        $focusContent = $this->focusModel->getContent($focus->toArray());
        $this->recombeeTokenReplacer->replaceTokensFromContent(
                $focusContent['focus'],
                $this->getOptionsBasedOnRecommendationsType($event->getConfig()['type'], $campaignId, $leadId)
            );

        // check if cart has some items
        if (!$this->recombeeTokenReplacer->hasItems()) {
            return $event->setFailed(
                'No recombee results for this contact #'.$leadId.' and  focus  #'.$focusId
            );
        }
        $tokens      = $this->recombeeTokenReplacer->getReplacedTokens();

        $values                 = [];
        $values['focus_item'][] = [
            'id' => $focusId,
            'js' => $this->router->generate(
                'mautic_recombee_js_generate_focus',
                ['id' => $focusId],
                true
            ),
        ];

        return $this->setResults(['event'=>$event, 'tokens'=>$tokens]);
    }


    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionDynamiContent(CampaignExecutionEvent $event)
    {

        if (!$event->checkContext('recombee.dynamic.content')) {
            return;
        }

        $slot             = $event->getConfig()['slot'];
        $dynamicContentId = (int) $event->getConfig()['dynamic_content'];
        $lead             = $event->getLead();

        if (!$dynamicContentId) {
            return $event->setResult('Dynamic COntent ID #'.$dynamicContentId.' doesn\'t exist.');
        }

        /** @var DynamicContent $dwc */
        $dwc = $this->dynamicContentModel->getEntity($dynamicContentId);;

        if ($dwc instanceof DynamicContent) {
            // Use translation if available
            list($ignore, $dwc) = $this->dynamicContentModel->getTranslatedEntity($dwc, $lead);

            if ($slot) {
                $this->dynamicContentModel->setSlotContentForLead($dwc, $lead, $slot);
            }

            $this->dynamicContentModel->createStatEntry($dwc, $lead, $slot);
            $event->setChannel('recombee-dynamic-content', $dynamicContentId);
            $result = [
                'type'       => $event->getConfig()['type'],
                'campaignId' => $event->getEvent()['campaign']['id'],
                'slot'       => $slot,
            ];

            return $event->setResult($result);
        }

        $this->setResults($event);
        return $event->setResult(array_merge($event->getResult(), ['slot'=> $slot]));
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionDynamiContentRemove(CampaignExecutionEvent $event)
    {

        if (!$event->checkContext('recombee.dynamic.content.remove')) {
            return;
        }

        $slot             = $event->getConfig()['slot'];
        $lead             = $event->getLead();

        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->delete(MAUTIC_TABLE_PREFIX.'dynamic_content_lead_data')
            ->andWhere($qb->expr()->eq('slot', ':slot'))
            ->andWhere($qb->expr()->eq('lead_id', ':lead_id'))
            ->setParameter('slot', $slot)
            ->setParameter('lead_id', $lead->getId())
            ->execute();

        $event->setChannel('recombee-dynamic-content');
        return $this->setResults($event);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    private function setResults(CampaignExecutionEvent $event)
    {
       return $event->setResult([
            'type'       => $event->getConfig()['type'],
            'campaignId' => $event->getEvent()['campaign']['id'],
        ]);
    }

    /**
     * @param     $config
     * @param int $campaignId
     * @param int $leadId
     *
     * @return array
     */
    private function getOptionsBasedOnRecommendationsType(array $config, $campaignId, $leadId)
    {
        $options = [];

        $type = $config['type'];

        switch ($type) {
            case 'abandoned_cart':
                $seconds = $this->campaignLeadDetails->getDiffSecondsFromAddedTime($campaignId, $leadId);
                $options = $this->getAbandonedCartOptions(1, $seconds);
                break;
            case 'advanced':
                if (!empty($config['filter'])) {
                    $options['filter'] = $config['filter'];
                }
                if (!empty($config['booster'])) {
                    $options['booster'] = $config['booster'];
                }
                break;
        }

        return $options;
    }

    /**
     * @param $cartMinAge
     * @param $cartMaxAge
     *
     * @return array
     */
    private function getAbandonedCartOptions($cartMinAge, $cartMaxAge)
    {
        return [
            "expertSettings" => [
                "algorithmSettings" => [
                    "evaluator" => [
                        "name" => "reql",
                    ],
                    "model"     => [
                        "name"     => "reminder",
                        "settings" => [
                            "parameters" => [
                                "interaction-types"        => [
                                    "detail-view"   => [
                                        "enabled" => false,
                                    ],
                                    "cart-addition" => [
                                        "enabled" => true,
                                        "weight"  => 1.0,
                                        "min-age" => $cartMinAge,
                                        "max-age" => $cartMaxAge,
                                    ],
                                ],
                                "filter-purchased-max-age" => $cartMaxAge,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerCondition(CampaignExecutionEvent $event)
    {
        $lead = $event->getLead();

        if (!$lead || !$lead->getId()) {
            return $event->setResult(false);
        }
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerDecisionInjectRecombeeFocus2(CampaignExecutionEvent $event)
    {
        $focusId      = (int) $event->getConfig()['focus'];
        $eventDetails = $event->getEventDetails();
        $eventConfig  = $event->getConfig();
        if (!$focusId) {
            return $event->setResult(false);
        }
        // STOP sent campaignEventModel just if Focus Item is opened
        if (empty($eventDetails['stop']) && !empty($eventDetails['hit'])) {
            $hit = $eventDetails['hit'];
            // Limit to URLS
            if (!empty($eventConfig['urls']['list'])) {
                $limitToUrl = $eventConfig['urls']['list'];
                $isUrl      = false;
                foreach ($limitToUrl as $url) {
                    if (preg_match('/'.preg_quote($url, '/').'/i', $hit->getUrl())) {
                        $isUrl = true;
                    }
                }
                // page hit url doesn't match
                if (!$isUrl) {
                    return $event->setResult(false);
                }
            }
            // Set Focus Item JS url to session
            $values                 = [];
            $values['focus_item'][] = [
                'id' => $focusId,
                'js' => $this->router->generate('mautic_focus_generate', ['id' => $focusId], true),
            ];
            $this->trackingHelper->updateSession($values);
            return $event->setResult(false);
        } elseif (!empty($eventDetails['stop']) && !empty($eventDetails['focus']) && $eventDetails['focus']->getId() == $focusId) {
            // Decision return true If we trigger it on open event
            return $event->setResult(true);
        } else {
            return $event->setResult(false);
        }
    }


    /**
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent
     */
    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        $lead = $event->getLead();

        if ($this->leadModel->isContactable($lead, 'notification') !== DNC::IS_CONTACTABLE) {
            return $event->setFailed('mautic.notification.campaign.failed.not_contactable');
        }

        $notificationId = (int) $event->getConfig()['notification'];

        /** @var \Mautic\NotificationBundle\Entity\Notification $notification */
        $notification = $this->notificationModel->getEntity($notificationId);

        if ($notification->getId() !== $notificationId) {
            return $event->setFailed('mautic.notification.campaign.failed.missing_entity');
        }

        if (!$notification->getIsPublished()) {
            return $event->setFailed('mautic.notification.campaign.failed.unpublished');
        }

        // If lead has subscribed on multiple devices, get all of them.
        /** @var \Mautic\NotificationBundle\Entity\PushID[] $pushIDs */
        $pushIDs = $lead->getPushIDs();

        $playerID = [];

        foreach ($pushIDs as $pushID) {
            // Skip non-mobile PushIDs if this is a mobile event
            if ($event->checkContext('notification.send_mobile_notification') && $pushID->isMobile() == false) {
                continue;
            }

            // Skip mobile PushIDs if this is a non-mobile event
            if ($event->checkContext('notification.send_notification') && $pushID->isMobile() == true) {
                continue;
            }

            $playerID[] = $pushID->getPushID();
        }

        if (empty($playerID)) {
            return $event->setFailed('mautic.notification.campaign.failed.not_subscribed');
        }

        if ($url = $notification->getUrl()) {
            $url = $this->notificationApi->convertToTrackedUrl(
                $url,
                [
                    'notification' => $notification->getId(),
                    'lead'         => $lead->getId(),
                ],
                $notification
            );
        }

        /** @var TokenReplacementEvent $tokenEvent */
        $tokenEvent = $this->dispatcher->dispatch(
            NotificationEvents::TOKEN_REPLACEMENT,
            new TokenReplacementEvent(
                $notification->getMessage(),
                $lead,
                ['channel' => ['notification', $notification->getId()]]
            )
        );

        /** @var NotificationSendEvent $sendEvent */
        $sendEvent = $this->dispatcher->dispatch(
            NotificationEvents::NOTIFICATION_ON_SEND,
            new NotificationSendEvent($tokenEvent->getContent(), $notification->getHeading(), $lead)
        );

        // prevent rewrite notification entity
        $sendNotification = clone $notification;
        $sendNotification->setUrl($url);
        $sendNotification->setMessage($sendEvent->getMessage());
        $sendNotification->setHeading($sendEvent->getHeading());

        $response = $this->notificationApi->sendNotification(
            $playerID,
            $sendNotification
        );

        $event->setChannel('notification', $notification->getId());

        // If for some reason the call failed, tell mautic to try again by return false
        if ($response->code !== 200) {
            return $event->setResult(false);
        }

        $this->notificationModel->createStatEntry($notification, $lead, 'campaign.event', $event->getEvent()['id']);
        $this->notificationModel->getRepository()->upCount($notificationId);

        $result = [
            'status'  => 'mautic.notification.timeline.status.delivered',
            'type'    => 'mautic.notification.notification',
            'id'      => $notification->getId(),
            'name'    => $notification->getName(),
            'heading' => $sendEvent->getHeading(),
            'content' => $sendEvent->getMessage(),
        ];

        $event->setResult($result);
    }

}
