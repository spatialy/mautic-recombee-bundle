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

        <div style="display:none">
            <?php echo $view['form']->row($form['recommendationsType']); ?>
            <?php echo $view['form']->row($form['filter']); ?>
            <?php echo $view['form']->row($form['boost']); ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php echo $view['form']->label($form['pageTemplate']); ?>
    </div>
    <div class="col-md-6">
        <?php echo $view['form']->label($form['emailTemplate']); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-control-custom">
            <?php
            echo $view['form']->widget($form['pageTemplate']['header']);
            ?>
            <div class="form-control-custom-disabled">{% for item in items %}</div>
            <?php
            echo $view['form']->widget($form['pageTemplate']['body']);
            ?>
            <div class="form-control-custom-disabled">{% endfor %}</div>
            <?php
            echo $view['form']->widget($form['pageTemplate']['footer']);
            ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-control-custom">
            <?php
            echo $view['form']->widget($form['emailTemplate']['header']);
            echo $view['form']->errors($form['emailTemplate']['header']);
            ?>
            <div class="form-control-custom-disabled">{% for item in items %}</div>
            <?php
            echo $view['form']->widget($form['emailTemplate']['body']);
            ?>
            <div class="form-control-custom-disabled">{% endfor %}</div>
            <?php
            echo $view['form']->widget($form['emailTemplate']['footer']);
            ?>
        </div>
    </div>
</div>


<?php

echo $view['assets']->includeScript('plugins/MauticRecombeeBundle/Assets/js/recombee.js');
echo $view['assets']->includeStylesheet('plugins/MauticRecombeeBundle/Assets/css/recombee.css');
?>
<br>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('rightFormContent'); ?>
<?php echo $view['form']->row($form['isPublished']); ?>

<hr />
<h5><?php echo $view['translator']->trans('mautic.plugin.recombee.template.tags'); ?></h5>
<br />
<?php

$body = '<div class="row">';
foreach ($properties as $property) {
    $body .= '<div class="col-sm-6">';
    $body .= '{{ '.$property['name'].' }}';
    $body .= '</div>';
}
$body .= '</div>';
echo $body;
?>
<div class="ide">
    <?php echo $view['form']->rest($form); ?>
</div>


<?php $view['slots']->stop(); ?>

