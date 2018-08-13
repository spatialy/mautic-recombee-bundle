<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\EventListener;

use Mautic\CampaignBundle\Model\EventModel;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PageBundle\Event as Events;
use Mautic\LeadBundle\LeadEvent;
use MauticPlugin\MauticFocusBundle\Entity\Focus;
use MauticPlugin\MauticFocusBundle\FocusEvents;
use MauticPlugin\MauticFocusBundle\Model\FocusModel;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class FocusTokenSubscriber.
 */
class FocusTokenSubscriber extends CommonSubscriber
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var EventModel
     */
    private $eventModel;

    /**
     * @var FocusModel
     */
    private $focusModel;

    /**
     * FocusSubscriber constructor.
     *
     * @param Session    $session
     * @param EventModel $eventModel
     * @param FocusModel $focusModel
     */
    public function __construct(Session $session, EventModel $eventModel, FocusModel $focusModel)
    {

        $this->session = $session;
        $this->eventModel = $eventModel;
        $this->focusModel = $focusModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FocusEvents::TOKEN_REPLACEMENT => ['onTokenReplacement', 500],
        ];
    }

    /**
     * @param TokenReplacementEvent $event
     */
    public function onTokenReplacement(TokenReplacementEvent $event)
    {
        $sessionName       = $this->request->get('recombee');
        if (empty($event->getClickthrough()['focus_id'])) {
            return;
        }

        $focus = $this->focusModel->getEntity();
        if (!$focus) {
            return;
        }

        $reponse = $this->eventModel->triggerEvent('recombee.focus.view', ['focus' => $focus], 'focus',
            $focus->getId());
        if (empty($reponse['tokens'])) {
            return;
        }
        $tokens = $reponse['tokens'];

        /** @var Lead $lead */
        $content = $event->getContent();
        if ($content) {
            foreach ($tokens as $key => $tokenContent) {
                $content = str_replace($key, $tokenContent, $content);

            }
            $event->setContent($content);
        }
    }
}
