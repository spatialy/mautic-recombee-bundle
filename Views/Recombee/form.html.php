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
    <div class="col-md-6">
        <?php echo $view['form']->row($form['name']); ?>
    </div>
    <div class="col-md-6">
        <?php echo $view['form']->row($form['numberOfItems']); ?>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                <div id="recombee_properties_1"
                     data-show-on="{&quot;recombee_templateType_0&quot;:&quot;checked&quot;}">

                  <!--      <iframe id="recombee-preview" src="<?php /*echo $view['router']->generate('mautic_recombee_generate_template', ['id' => $recombee->getId()]); */?>" class="col-sm-12"></iframe>-->

                        <h3>Preview</h3>
                        <style>
                            .recombee-row {
                                display: grid;
                                grid-template-columns: repeat(12, 1fr);
                            }

                            .recombee-col {
                                grid-column: span <?php echo $recombee->getProperties()['columns']; ?>;
                            }

                            .recombee-image {
                                display: block;
                                width: 100%;
                                object: fit;;
                            }
                        </style>
                        <div class="recombee-row">
                            <?php for ($i = 0; $i < $recombee->getNumberOfItems(); $i++): ?>
                                <div class="recombee-col">
                                    <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
                                    <a href="<?php echo $recombee->getProperties()['itemName']; ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($recombee->getProperties()['itemImage'])): ?>
                                            <img class="recombee-image" src="http://via.placeholder.com/350" alt="">
                                        <?php endif; ?>
                                        <?php if (!empty($recombee->getProperties()['itemName'])): ?>
                                            <h5 class="recombee-name"><?php echo $recombee->getProperties(
                                                )['itemName']; ?></h5>
                                        <?php endif; ?>
                                        <?php if (!empty($recombee->getProperties()['itemShortDescription'])): ?>
                                            <p class="recombe-short-description"><?php echo $recombee->getProperties(
                                                )['itemShortDescription']; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($recombee->getProperties()['itemPrice'])): ?>
                                            <p class="recombee-price"><?php echo $recombee->getProperties(
                                                )['itemPrice']; ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($recombee->getProperties()['action'])): ?>
                                            <a class="recombee-action"><?php echo $recombee->getProperties(
                                                )['action']; ?></a>
                                        <?php endif; ?>

                                        <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
                                    </a>
                                <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
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
<?php echo $view['form']->row($form['isPublished']); ?>
<?php echo $view['form']->row($form['templateType']); ?>


<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <i class="fa fa-bullseye"></i> <?php echo $view['translator']->trans('mautic.plugin.recombee.item.name'); ?>

                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <?php
                echo $view['form']->widget($form['properties']['itemName']);
                ?>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Collapsible Group Item #2
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingThree">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Collapsible Group Item #3
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
            <div class="panel-body">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
            </div>
        </div>
    </div>
</div>
<?php echo $view['form']->row($form['properties']); ?>


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

