<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="analytics-case">
    <div class="col-xs-12 va-m mb-20">
        <?php echo $view->render(
            'MauticExtendeeAnalyticsBundle:Analytics:header.html.php',
            ['tags' => $tags]
        ); ?>
    </div>
    <?php echo $view->render(
        'MauticExtendeeAnalyticsBundle:Analytics:data.html.php',
        [
            'metrics'  => $metrics,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]
    ); ?>
</div>

<script>
    var CLIENT_ID = '<?php echo $keys['clientId'] ?>';
    var ids = 'ga:<?php echo $keys['viewId']; ?>';
    var metrics = '<?php echo implode(',', array_keys($rawMetrics)); ?>';
    var filters = '<?php echo $filters ?>';
    var currency = '<?php echo $keys['currency']; ?>';
    var dateFrom = '<?php echo (new \Mautic\CoreBundle\Helper\DateTimeHelper($dateFrom))->toLocalString('Y-m-d'); ?>';
    var dateTo = '<?php echo (new \Mautic\CoreBundle\Helper\DateTimeHelper($dateTo))->toLocalString('Y-m-d'); ?>';
    var metricsGraph = 'ga:sessions';
    <?php if (!empty($metrics['ecommerce'])) { ?>
    metricsGraph = metricsGraph + ',ga:transactions';
    <?php } ?>
    if (typeof analyticsReady == 'undefined') {
        var analyticsReady = false;
    }
</script>
<?php echo $view['assets']->includeScript(
    'plugins/MauticExtendeeAnalyticsBundle/Assets/js/analytics.js?time='.time()
); ?>
