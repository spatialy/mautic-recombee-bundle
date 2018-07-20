<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!isset($preview)) {
    $preview = false;
}


echo $view->render(
    'MauticRecombeeBundle:Recombee:generator-header.html.php',
    [
        'recombee' => $recombee,
        'preview' => $preview
    ]
);
?>

<?php for ($i = 0; $i < $recombee->getNumberOfItems(); $i++): ?>
    <?php
    echo $view->render(
        'MauticRecombeeBundle:Recombee:generator-body.html.php',
        [
            'recombee' => $recombee,
            'preview' => $preview
        ]
    );
    ?>
<?php endfor; ?>
<?php
echo $view->render(
    'MauticRecombeeBundle:Recombee:generator-footer.html.php',
    [
        'recombee' => $recombee,
        'preview' => $preview
    ]
);
?>