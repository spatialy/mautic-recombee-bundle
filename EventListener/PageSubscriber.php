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
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\BuilderTokenHelper;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PageBundle\Event as Events;
use Mautic\PageBundle\PageEvents;
use Mautic\LeadBundle\LeadEvent;
use Mautic\PageBundle\Event\PageHitEvent;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenHTMLReplacer;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer;
use Recombee\RecommApi\Requests\AddDetailView;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;

/**
 * Class PageSubscriber.
 */
class PageSubscriber extends CommonSubscriber
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
     * @var ApiCommands
     */
    private $apiCommands;

    /**
     * @var RecombeeTokenHTMLReplacer
     */
    private $HTMLReplacer;

    /**
     * @var EventModel
     */
    private $eventModel;


    /**
     * PageSubscriber constructor.
     *
     * @param RecombeeHelper            $recombeeHelper
     * @param RecombeeTokenReplacer     $recombeeTokenReplacer
     * @param ApiCommands               $apiCommands
     * @param RecombeeTokenHTMLReplacer $HTMLReplacer
     * @param EventModel                $eventModel
     */
    public function __construct(
        RecombeeHelper $recombeeHelper,
        RecombeeTokenReplacer $recombeeTokenReplacer,
        ApiCommands $apiCommands,
        RecombeeTokenHTMLReplacer $HTMLReplacer,
        EventModel $eventModel
    ) {
        $this->recombeeHelper        = $recombeeHelper;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
        $this->apiCommands           = $apiCommands;
        $this->HTMLReplacer          = $HTMLReplacer;
        $this->eventModel = $eventModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PageEvents::PAGE_ON_BUILD   => ['onPageBuild', 0],
            PageEvents::PAGE_ON_HIT     => ['onPageHit', 0],
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
        ];
    }

    /**
     * Add forms to available page tokens.
     *
     * @param PageBuilderEvent $event
     */
    public function onPageBuild(Events\PageBuilderEvent $event)
    {
        if ($event->tokensRequested($this->recombeeHelper->getRecombeeRegex())) {
            $tokenHelper = new BuilderTokenHelper($this->factory, 'recombee');
            $event->addTokensFromHelper($tokenHelper, $this->recombeeHelper->getRecombeeRegex(), 'name', 'id', true);
        }
    }


    /**
     * Trigger actions for page hits.
     *
     * @param PageHitEvent $event
     */
    public function onPageHit(PageHitEvent $event)
    {
        $hit      = $event->getHit();
        if (!$hit->getRedirect() && !$hit->getEmail()) {
            $response = $this->eventModel->triggerEvent('recombee.focus.insert', ['hit' => $hit]);
        }

        $request = $event->getRequest();
        if (!empty($request->get('Recombee'))) {
            $commands = \GuzzleHttp\json_decode($request->get('Recombee'), true);
            foreach ($commands as $apiRequest => $options) {
                // try get userId If not set directly from tracking code
                if (!isset($options['userId'])) {
                    $options['userId'] = $event->getLead()->getId();
                }
                $this->apiCommands->callCommand($apiRequest, $options);
            }
        }

    }

    /**
     * @param PageDisplayEvent $event
     */
    public function onPageDisplay(Events\PageDisplayEvent $event)
    {
        if ($event->getPage()) {
            $event->setContent($this->recombeeTokenReplacer->replaceTokensFromContent($event->getContent()));
        }
    }
}
