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

<?php
$view['slots']->start('primaryFormContent');
echo $view['assets']->includeStylesheet('plugins/MauticRecombeeBundle/Assets/css/recombee.css');
echo $view['assets']->includeStylesheet('plugins/MauticRecombeeBundle/Assets/css/bootstrap-3-grid-min.css');
echo $view['assets']->includeScript('plugins/MauticRecombeeBundle/Assets/js/recombee.js');
/** @var \MauticPlugin\MauticRecombeeBundle\Entity\Recombee $recombee */
$recombee = $entity;
?>
<div class="row">
    <div class="col-md-5">
        <?php echo $view['form']->row($form['name']); ?>
    </div>
    <div class="col-md-4">
        <?php echo $view['form']->row($form['numberOfItems']); ?>
    </div>
    <div class="col-md-3">
        <?php echo $view['form']->row($form['isPublished']); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                <div id="recombee_properties_1"
                     data-show-on="{&quot;recombee_templateType_0&quot;:&quot;checked&quot;}">

                    <h3>Preview</h3>

                    <?php
                    echo $view->render(
                        'MauticRecombeeBundle:Recombee:generator.html.php',
                        [
                            'recombee'        => $recombee,
                        ]
                    );
                    ?>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="recombee_template_1" data-show-on="{&quot;recombee_templateType_1&quot;:&quot;checked&quot;}">

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
        </div>
    </div>
</div>

<br>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('rightFormContent'); ?>
<?php echo $view['form']->row($form['templateType']); ?>


<div class="row">
    <div class="col-md-12">
        <div id="recombee_template_2" data-show-on="{&quot;recombee_templateType_0&quot;:&quot;checked&quot;}">

            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"
                               aria-expanded="true" aria-controls="collapseOne">
                                <i class="fa fa-check text-success"></i>
                                <?php echo $view['translator']->trans('mautic.plugin.recombee.settings'); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel"
                         aria-labelledby="headingOne">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->row($form['properties']['columns']);
                            echo $view['form']->row($form['properties']['background']);
                            echo $view['form']->row($form['properties']['font']);
                            echo $view['form']->row($form['properties']['padding']);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="itemNameHead">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                               href="#itemName" aria-expanded="false" aria-controls="itemName">
                                <i class="fa fa-check <?php if (!empty($recombee->getProperties()['itemName'])
                                ): echo 'text-success'; endif; ?>"></i> <?php echo $view['translator']->trans(
                                    'mautic.plugin.recombee.item.name'
                                ); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="itemName" class="panel-collapse collapse" role="tabpanel" aria-labelledby="itemNameHead">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->widget($form['properties']['itemName']);
                            echo $view['form']->row($form['properties']['itemNameColor']);
                            echo $view['form']->row($form['properties']['itemNameSize']);
                            echo $view['form']->row($form['properties']['itemNamePadding']);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="itemImageHead">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                               href="#itemImage" aria-expanded="false" aria-controls="itemImage">
                                <i class="fa fa-check <?php if (!empty($recombee->getProperties()['itemImage'])
                                ): echo 'text-success'; endif; ?>"></i> <?php echo $view['translator']->trans(
                                    'mautic.plugin.recombee.item.image'
                                ); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="itemImage" class="panel-collapse collapse" role="tabpanel" aria-labelledby="itemImageHead">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->widget($form['properties']['itemImage']);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="itemShortDescriptionHead">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                               href="#itemShortDescription" aria-expanded="false" aria-controls="itemShortDescription">
                                <i class="fa fa-check <?php if (!empty($recombee->getProperties(
                                )['itemShortDescription'])
                                ): echo 'text-success'; endif; ?>"></i> <?php echo $view['translator']->trans(
                                    'mautic.plugin.recombee.item.short.description'
                                ); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="itemShortDescription" class="panel-collapse collapse" role="tabpanel"
                         aria-labelledby="itemShortDescriptionHead">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->widget($form['properties']['itemShortDescription']);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="itemActionHead">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                               href="#itemAction" aria-expanded="false" aria-controls="itemAction">
                                <i class="fa fa-check <?php if (!empty($recombee->getProperties()['itemAction'])
                                ): echo 'text-success'; endif; ?>"></i> <?php echo $view['translator']->trans(
                                    'mautic.plugin.recombee.item.action'
                                ); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="itemAction" class="panel-collapse collapse" role="tabpanel"
                         aria-labelledby="itemActionHead">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->widget($form['properties']['itemUrl']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemAction']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionSize']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionBackground']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionHover']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionColor']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionRadius']);
                            ?>
                            <?php
                            echo $view['form']->row($form['properties']['itemActionPadding']);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="itemPriceHead">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                               href="#itemPrice" aria-expanded="false" aria-controls="itemPrice">
                                <i class="fa fa-check <?php if (!empty($recombee->getProperties()['itemPrice'])
                                ): echo 'text-success'; endif; ?>"></i> <?php echo $view['translator']->trans(
                                    'mautic.plugin.recombee.item.price'
                                ); ?>
                            </a>
                        </h4>
                    </div>
                    <div id="itemPrice" class="panel-collapse collapse" role="tabpanel" aria-labelledby="itemPriceHead">
                        <div class="panel-body">
                            <?php
                            echo $view['form']->row($form['properties']['itemPrice']);
                            echo $view['form']->row($form['properties']['itemPriceColor']);
                            echo $view['form']->row($form['properties']['itemPriceSize']);
                            echo $view['form']->row($form['properties']['itemPricePadding']);
                            echo $view['form']->row($form['properties']['itemOldPrice']);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div id="recombee_template" data-show-on="{&quot;recombee_templateType_1&quot;:&quot;checked&quot;}">

            <div class="row">
                <div class="col-xs-12">
                    <hr>
                    <h5><?php echo $view['translator']->trans('mautic.plugin.recombee.template.tags'); ?></h5>
                    <br>
                </div>
            </div>
            <div class="row">
                <?php
                $body = '';
                if (!empty($properties)) {
                    foreach ($properties as $property) {
                        $body .= '<div class="col-sm-6">';
                        $body .= '{{ '.$property['name'].' }}';
                        $body .= '</div>';
                    }
                }
                echo $body;
                ?>
            </div>
        </div>
    </div>
</div>

<div class="ide">
    <?php echo $view['form']->rest($form); ?>
</div>


<?php $view['slots']->stop(); ?>

