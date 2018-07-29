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

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use Mautic\PointBundle\Model\PointModel;
use MauticPlugin\MauticRecombeeBundle\Helper\GoogleAnalyticsHelper;
use MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper;
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
     * @var RecombeeHelper
     */
    private $recombeeHelper;

    /**
     * @var GoogleAnalyticsHelper
     */
    private $analyticsHelper;

    /**
     * DashboardSubscriber constructor.
     *
     * @param RecombeeModel         $recombeeModel
     * @param RecombeeHelper        $recombeeHelper
     * @param GoogleAnalyticsHelper $analyticsHelper
     */
    public function __construct(RecombeeModel $recombeeModel, RecombeeHelper $recombeeHelper, GoogleAnalyticsHelper $analyticsHelper)
    {

        $this->recombeeModel = $recombeeModel;
        $this->recombeeHelper = $recombeeHelper;
        $this->analyticsHelper = $analyticsHelper;
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


        if ($event->getType() == 'recombee.analytics' && $this->analyticsHelper->enableRecombeeIntegration()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();
            if (!$event->isCached() || 1==1) {
                $recombeeEvents = $this->recombeeHelper->getRecombeeEvents();
                $this->analyticsHelper->setRecombeeEvents($recombeeEvents);
                $event->setTemplateData([
                    'tags'   =>     $this->analyticsHelper->getFlatUtmTags(),
                    'keys'       => $this->analyticsHelper->getAnalyticsFeatures(),
                    'filters'    => $this->analyticsHelper->getFilter(),
                    'metrics'    => $this->analyticsHelper->getMetricsFromConfig(),
                    'rawMetrics' => $this->analyticsHelper->getRawMetrics(),
                    'dateFrom' =>  (new DateTimeHelper($params['dateFrom']))->toLocalString('Y-m-d'),
                    'dateTo' =>  (new DateTimeHelper($params['dateTo']))->toLocalString('Y-m-d'),
                ]);
            }

                $event->setTemplate('MauticRecombeeBundle:Analytics:analytics-dashboard.html.php');
            $event->stopPropagation();
        }
    }
}
