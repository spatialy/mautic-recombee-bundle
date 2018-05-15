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

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\BuilderTokenHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonSubscriber
{
    /**
     * @var RecombeeHelper
     */
    protected $recombeeHelper;

    /**
     * @var RecombeeTokenReplacer
     */
    private $recombeeTokenReplacer;


    /**
     * EmailSubscriber constructor.
     *
     * @param RecombeeHelper        $recombeeHelper
     * @param RecombeeTokenReplacer $recombeeTokenReplacer
     */
    public function __construct(RecombeeHelper $recombeeHelper, RecombeeTokenReplacer $recombeeTokenReplacer)
    {
        $this->recombeeHelper        = $recombeeHelper;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }


    /**
     * @param EmailSendEvent $event
     */
    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailGenerate(EmailSendEvent $event)
    {
        if ($event->getEmail()) {
            $event->setContent($this->recombeeTokenReplacer->replaceEmailTokens($event->getContent()));
        }
    }
}
