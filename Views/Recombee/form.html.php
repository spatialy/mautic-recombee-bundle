<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view->extend('MauticCoreBundle:FormTheme:form_simple.html.php');

?>

<?php $view['slots']->start('primaryFormContent'); ?>
<div class="row">
    <div class="col-md-6">
        <?php echo $view['form']->row($form['name']); ?>
    </div>
    <div class="col-md-6">
        <?php echo $view['form']->row($form['numberOfItems']); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php echo $view['form']->label($form['template']); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-control-custom">
            <?php
            echo $view['form']->widget($form['template']['header']);
            ?>
            <div class="form-control-custom-disabled">{% for item in items %}</div>
            <?php
            echo $view['form']->widget($form['template']['body']);
            ?>
            <div class="form-control-custom-disabled">{% endfor %}</div>
            <?php
            echo $view['form']->widget($form['template']['footer']);
            ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <hr />
        <h5><?php echo $view['translator']->trans('mautic.plugin.recombee.template.tags'); ?></h5>
        <br>
    </div>
</div>
<div class="row">
    <?php
    $body = '';
    if (!empty($properties)) {
        foreach ($properties as $property) {
            $body .= '<div class="col-sm-4">';
            $body .= '{{ '.$property['name'].' }}';
            $body .= '</div>';
        }
    }
    echo $body;
    ?>
</div>


<?php

echo $view['assets']->includeStylesheet('plugins/MauticRecombeeBundle/Assets/css/recombee.css');
?>
<br>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('rightFormContent'); ?>
<?php echo $view['form']->row($form['isPublished']); ?>

<div class="ide">
    <?php echo $view['form']->rest($form); ?>
</div>


<?php $view['slots']->stop(); ?>

