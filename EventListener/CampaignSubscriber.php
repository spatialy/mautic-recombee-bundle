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

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\NotificationBundle\Form\Type\MobileNotificationSendType;
use Mautic\NotificationBundle\Form\Type\NotificationSendType;
use Mautic\PageBundle\Helper\TrackingHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticFocusBundle\Entity\Focus;
use MauticPlugin\MauticFocusBundle\Model\FocusModel;
use MauticPlugin\MauticRecombeeBundle\EventListener\Service\CampaignLeadDetails;
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
     * @var EmailModel
     */
    protected $messageQueueModel;


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
     * @param LeadModel             $leadModel
     * @param EmailModel            $emailModel
     * @param EventModel            $eventModel
     * @param MessageQueueModel     $messageQueueModel
     * @param SendEmailToUser       $sendEmailToUser
     * @param RecombeeTokenReplacer $recombeeTokenReplacer
     * @param CampaignLeadDetails   $campaignLeadDetails
     * @param TrackingHelper        $trackingHelper
     * @param FocusModel            $focusModel
     * @param Session               $session
     * @param IntegrationHelper     $integrationHelper
     */
    public function __construct(
        LeadModel $leadModel,
        EmailModel $emailModel,
        EventModel $eventModel,
        MessageQueueModel $messageQueueModel,
        SendEmailToUser $sendEmailToUser,
        RecombeeTokenReplacer $recombeeTokenReplacer,
        CampaignLeadDetails $campaignLeadDetails,
        TrackingHelper $trackingHelper,
        FocusModel $focusModel,
        Session $session,
        IntegrationHelper $integrationHelper
    ) {
        $this->leadModel             = $leadModel;
        $this->emailModel            = $emailModel;
        $this->campaignEventModel    = $eventModel;
        $this->messageQueueModel     = $messageQueueModel;
        $this->sendEmailToUser       = $sendEmailToUser;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
        $this->campaignLeadDetails   = $campaignLeadDetails;
        $this->trackingHelper        = $trackingHelper;
        $this->focusModel            = $focusModel;
        $this->session               = $session;
        $this->integrationHelper     = $integrationHelper;
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
                ['onCampaignTriggerActionInjectRecombeeFocus', 1],
                ['onCampaignTriggerActionSendNotification', 2],
            ],
            RecombeeEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {

        $event->addAction(
            'recombee.email.send',
            [
                'label'           => 'mautic.recombee.email.campaign.event.send',
                'description'     => 'mautic.recombee.email.campaign.event.send.desc',
                'eventName'       => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => RecombeeEmailSendType::class,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_email'],
                'channel'         => 'recombee',
                'channelIdField'  => 'email',
            ]
        );

        $event->addAction(
            'recombee.focus',
            [
                'label'                  => 'mautic.recombee.focus.campaign.event.send',
                'description'            => 'mautic.recombee.focus.campaign.event.send.desc',
                'eventName'              => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'               => RecombeeFocusType::class,
                'formTypeOptions'        => ['update_select' => 'campaignevent_properties_focus'],
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
        );

        /*
         * Notification postpone at the moment
         *
         * $integration = $this->integrationHelper->getIntegrationObject('OneSignal');
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
                        'channel'          => 'mobile_notification',
                        'channelIdField'   => 'mobile_notification',
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
                    'channel'          => 'notification',
                    'channelIdField'   => 'notification',
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
        $emailId    = (int) $config['email'];
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

        $emailSent = $this->emailModel->sendEmail($email, $leadCredentials, $options);
        if (is_array($emailSent)) {
            $errors = implode('<br />', $emailSent);

            // Add to the metadata of the failed event
            $emailSent = [
                'result' => false,
                'errors' => $errors,
            ];
        } elseif (true !== $emailSent) {
            $emailSent = [
                'result' => false,
                'errors' => $emailSent,
            ];
        }

        return $event->setResult($emailSent);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionInjectRecombeeFocus(CampaignExecutionEvent $event)
    {
        $focusId = (int) $event->getConfig()['focus']['focus'];
        if (!$focusId) {
            return $event->setResult('Focus ID #'.$focusId.' doesn\'t exist.');
        }

        /** @var Focus $focus */
        $focus = $this->focusModel->getEntity($focusId);

        if (!$focus) {
            return $event->setResult(false);
        }

        $campaignId = $event->getEvent()['campaign']['id'];
        $leadId     = $event->getLead()->getId();


        $event->setChannel('recombee-focus', $focusId);
        $focusContent = $this->focusModel->getContent($focus->toArray());
        $content      =
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
        $contentHash = md5(serialize($tokens));
        $this->session->set($contentHash, serialize($tokens));

        $values                 = [];
        $values['focus_item'][] = [
            'id' => $focusId,
            'js' => $this->router->generate(
                'mautic_recombee_js_generate_focus',
                ['id' => $focusId, 'recombee' => $contentHash],
                true
            ),
        ];
        $this->trackingHelper->updateSession($values);

        return $event->setResult(true);
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

}
