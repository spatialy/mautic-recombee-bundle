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
use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\Exception\EmailCouldNotBeSentException;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticRecombeeBundle\RecombeeEvents;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer;

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
     * @var EventModel
     */
    protected $campaignEventModel;

    /**
     * @var SendEmailToUser
     */
    private $sendEmailToUser;

    /**
     * @var RecombeeTokenReplacer
     */
    private $recombeeTokenReplacer;


    /**
     * @param LeadModel             $leadModel
     * @param EmailModel            $emailModel
     * @param EventModel            $eventModel
     * @param MessageQueueModel     $messageQueueModel
     * @param SendEmailToUser       $sendEmailToUser
     * @param RecombeeTokenReplacer $recombeeTokenReplacer
     * @param CampaignModel         $campaignModel
     */
    public function __construct(
        LeadModel $leadModel,
        EmailModel $emailModel,
        EventModel $eventModel,
        MessageQueueModel $messageQueueModel,
        SendEmailToUser $sendEmailToUser,
        RecombeeTokenReplacer $recombeeTokenReplacer,
        CampaignModel $campaignModel
    ) {
        $this->leadModel           = $leadModel;
        $this->emailModel          = $emailModel;
        $this->campaignEventModel  = $eventModel;
        $this->messageQueueModel   = $messageQueueModel;
        $this->sendEmailToUser     = $sendEmailToUser;
        $this->campaignModel       = $campaignModel;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD          => ['onCampaignBuild', 0],
            RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerActionSendAbandonedEmail', 0],
            ],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {

        $event->addAction(
            'abandoned.email.send',
            [
                'label'           => 'mautic.recombee.abandoned.email.campaign.event.send',
                'description'     => 'mautic.recombee.abandoned.email.campaign.event.send.desc',
                'eventName'       => RecombeeEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => 'emailsend_list',
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_email'],
                'formTheme'       => 'MauticEmailBundle:FormTheme\EmailSendList',
                'channel'         => 'recombee',
                'channelIdField'  => 'email',
            ]
        );

    }

    /**
     * Triggers the action which sends email to contact.
     *
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent|null
     */
    public function onCampaignTriggerActionSendAbandonedEmail(CampaignExecutionEvent $event)
    {

        if (!$event->checkContext('abandoned.email.send')) {
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

        $leadCampaignRepo    = $this->campaignModel->getCampaignLeadRepository();
        $leadsCampaignDetail = $leadCampaignRepo->getLeadDetails($campaignId, [$leadId]);

        if (empty($leadsCampaignDetail[$leadId])) {
            return $event->setFailed('Contact does not exist in campaign. Details empty');
        }

        $leadsCampaignDetail = end($leadsCampaignDetail[$leadId]);
        $seconds             = (new \DateTime('now'))->getTimestamp() - $leadsCampaignDetail['dateAdded']->getTimestamp(
            );

        $options = [
            'source'        => ['campaign.event', $event->getEvent()['id']],
            'return_errors' => true,
            'dnc_as_error'  => true,
        ];
        $event->setChannel('recombee-abandoned-email', $emailId);
        $email->setCustomHtml($this->recombeeTokenReplacer->replaceTokensFromContent($email->getCustomHtml(), $this->getAbandonedCartOptions(1, $seconds)));

        // check if cart has some items
        if (!$this->recombeeTokenReplacer->isHasItems()) {
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
                                    "detail-view" => [
                                        "enabled" => false
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
     * Triggers the action which sends email to user, contact owner or specified email addresses.
     *
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent|null
     */
    public function onCampaignTriggerActionSendEmailToUser(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('email.send.to.user')) {
            return;
        }

        $config = $event->getConfig();
        $lead   = $event->getLead();

        try {
            $this->sendEmailToUser->sendEmailToUsers($config, $lead);
            $event->setResult(true);
        } catch (EmailCouldNotBeSentException $e) {
            $event->setFailed($e->getMessage());
        }

        return $event;
    }
}
