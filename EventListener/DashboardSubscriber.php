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

use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use Mautic\PointBundle\Model\PointModel;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;

/**
 * Class DashboardSubscriber.
 */
class DashboardSubscriber extends MainDashboardSubscriber
{
    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'recombee';

    /**
     * Define the widget(s).
     *
     * @var string
     */
    protected $types = [
        'recombee.analytics' => [],
    ];

    /**
     * Define permissions to see those widgets.
     *
     * @var array
     */
    protected $permissions = [
        'recombee:recombee:viewown',
        'recombee:recombee:viewother',
    ];

    /**
     * @var RecombeeModel
     */
    private $recombeeModel;

    /**
     * DashboardSubscriber constructor.
     *
     * @param RecombeeModel $recombeeModel
     */
    public function __construct(RecombeeModel $recombeeModel)
    {

        $this->recombeeModel = $recombeeModel;
    }


    /**
     * Set a widget detail when needed.
     *
     * @param WidgetDetailEvent $event
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        $this->checkPermissions($event);
        $canViewOthers = $event->hasPermission('recombee:recombee:viewother');

        if ($event->getType() == 'recombee.analytics') {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $event->setTemplateData([]);
            }

            $event->setTemplate('MauticRecombeeBundle:SubscribedEvents:Dashboard/Recombee.google.analytics.html.php');
            $event->stopPropagation();
        }
    }
}
