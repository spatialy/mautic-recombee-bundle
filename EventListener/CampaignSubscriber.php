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
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Event\EmailOpenEvent;
use Mautic\EmailBundle\Event\EmailReplyEvent;
use Mautic\EmailBundle\Exception\EmailCouldNotBeSentException;
use Mautic\EmailBundle\Helper\UrlMatcher;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Entity\Hit;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\RecombeeEvents;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeGenerator;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenFinder;

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
     * @var ApiCommands
     */
    private $apiCommands;

    /**
     * @var RecombeeTokenFinder
     */
    private $recombeeTokenFinder;

    /**
     * @var RecombeeGenerator
     */
    private $recombeeGenerator;

    /**
     * @param LeadModel           $leadModel
     * @param EmailModel          $emailModel
     * @param EventModel          $eventModel
     * @param MessageQueueModel   $messageQueueModel
     * @param SendEmailToUser     $sendEmailToUser
     * @param ApiCommands         $apiCommands
     * @param RecombeeTokenFinder $recombeeTokenFinder
     * @param RecombeeGenerator   $recombeeGenerator
     */
    public function __construct(
        LeadModel $leadModel,
        EmailModel $emailModel,
        EventModel $eventModel,
        MessageQueueModel $messageQueueModel,
        SendEmailToUser $sendEmailToUser,
        ApiCommands $apiCommands,
        RecombeeTokenFinder $recombeeTokenFinder,
        RecombeeGenerator $recombeeGenerator
    ) {
        $this->leadModel           = $leadModel;
        $this->emailModel          = $emailModel;
        $this->campaignEventModel  = $eventModel;
        $this->messageQueueModel   = $messageQueueModel;
        $this->sendEmailToUser     = $sendEmailToUser;
        $this->apiCommands         = $apiCommands;
        $this->recombeeTokenFinder = $recombeeTokenFinder;
        $this->recombeeGenerator   = $recombeeGenerator;
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
    public function onCampaignTriggerActionSendEmailToContact(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('abandoned.email.send')) {
            return;
        }

        $leadCredentials = $event->getLeadFields();

        if (empty($leadCredentials['email'])) {
            return $event->setFailed('Contact does not have an email');
        }

        $config  = $event->getConfig();
        $emailId = (int) $config['email'];
        $email   = $this->emailModel->getEntity($emailId);

        if (!$email || !$email->isPublished()) {
            return $event->setFailed('Email not found or published');
        }

        $options = [
            'source'        => ['campaign.event', $event->getEvent()['id']],
            'return_errors' => true,
            'dnc_as_error'  => true,
        ];

        $event->setChannel('email', $emailId);

        $recombeeOptions = [
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
                                    "cart-addition" => [
                                        "enabled" => true,
                                        "weight"  => 1.0,
                                        "min-age" => 00,
                                        "max-age" => 64800.0,
                                    ],
                                ],
                                "filter-purchased-max-age" => 1209600,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $content  = $email->getContent();
        $tokens   = $this->recombeeTokenFinder->findTokens($content);
        $hasItems = false;
        if (!empty($tokens)) {
            foreach ($tokens as $key => $token) {
                $items = $this->recombeeGenerator->getResultByToken($token, $recombeeOptions);
                if (!empty($items)) {
                    $hasItems = true;
                }
            }
        }
        // check if cart has some
        if (!$hasItems) {
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
