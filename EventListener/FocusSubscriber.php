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

use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PageBundle\Event as Events;
use Mautic\LeadBundle\LeadEvent;
use MauticPlugin\MauticFocusBundle\FocusEvents;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class PageSubscriber.
 */
class FocusSubscriber extends CommonSubscriber
{
    /**
     * @var Session
     */
    private $session;

    /**
     * FocusSubscriber constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {

        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FocusEvents::TOKEN_REPLACEMENT => ['onTokenReplacement', 0],
        ];
    }

    /**
     * @param TokenReplacementEvent $event
     */
    public function onTokenReplacement(TokenReplacementEvent $event)
    {
        $sessionName = $this->request->get('recombee');
        $tokensFromSession = $this->session->get($sessionName);
        if (empty($event->getClickthrough()['focus_id']) || !$tokensFromSession) {
            return;
        }

        /** @var Lead $lead */
        $content = $event->getContent();
        $tokens  = unserialize($tokensFromSession);
        foreach ($tokens as $key => $tokenContent) {
            $content = str_replace($key, $tokenContent, $content);

        }
        $event->setContent($content);

        $this->session->remove($sessionName);
    }
}
