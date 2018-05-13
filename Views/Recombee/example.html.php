<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
echo $view['assets']->includeScript('plugins/MauticRecombeeBundle/Assets/js/recombee.js');
?>
<?php if ($tmpl == 'index'): ?>
    <div class="row lead-merge-form">
    <div class="col-xs-6 pr-0">
        <?php echo $view->render('MauticCoreBundle:Helper:search.html.php', [
            'searchId' => (empty($searchId)) ? null : $searchId,
            'searchValue' => $searchValue,
            'action' => $currentRoute,
            'searchHelp' => false,
            'target' => '.lead-merge-options',
            'tmpl' => 'update',
        ]); ?>
    </div>
    <div class="lead-merge-options">
<?php endif; ?>

<?php echo $view['form']->start($form); ?>
    <div class="col-xs-6">
        <?php echo $view['form']->widget($form['id_to_example']); ?>
    </div>
<?php echo $view['form']->end($form); ?>

        <?php if ($tmpl == 'index'): ?>
    </div>
    </div>
<?php endif; ?>
