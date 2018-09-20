<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$class = 'recombee-template-'.$recombee->getId();

?>
<?php
echo $view->render(
    'MauticRecombeeBundle:Builder\Page:generator-css.html.php',
    [
        'recombee' => $recombee,
        'settings' => $settings,
        'preview' => $preview
    ]
);
?>

<div class="recombee-global-row <?php echo $class ?>">

    <?php if ($preview) {
        echo html_entity_decode($recombee->getProperties()['header']);
        ?>
    <?php } else {
        echo $recombee->getProperties()['header']; ?>
    <?php } ?>
    <div class="recombee-row">
