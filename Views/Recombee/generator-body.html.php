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
<div class="recombee-col">
    <?php if (!empty($recombee->getProperties()['itemImage'])): ?>
        <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
            <a  href="<?php echo $recombee->getProperties()['itemUrl']; ?>">
        <?php endif; ?>
        <?php if (isset($preview) && $preview){ ?>
            <img class="recombee-image" src="http://via.placeholder.com/350x250?text=Example" alt="">
        <?php }else{ ?>
            <img class="recombee-image" src="<?php echo $recombee->getProperties()['itemImage']; ?>" alt="">
        <?php } ?>
        <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($recombee->getProperties()['itemName'])): ?>
        <h5 class="recombee-name"><?php echo $recombee->getProperties()['itemName']; ?></h5>
    <?php endif; ?>
    <?php if (!empty($recombee->getProperties()['itemShortDescription'])): ?>
        <p class="recombe-short-description"><?php echo $recombee->getProperties()['itemShortDescription']; ?></p>
    <?php endif; ?>
    <?php if (!empty($recombee->getProperties()['itemPrice'])): ?>
        <p class="recombee-price-case">
            <span class="recombee-price"><?php echo $recombee->getProperties()['itemPrice']; ?></span>
            <?php if (!empty($recombee->getProperties()['itemOldPrice'])): ?>
                <span class="recombee-price-old"
                      style="text-decoration: line-through"><?php echo $recombee->getProperties(
                    )['itemOldPrice']; ?></span>
            <?php endif; ?>
        </p>
    <?php endif; ?>


    <?php if (!empty($recombee->getProperties()['itemAction'])): ?>
        <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
            <a class="recombee-action" href="<?php echo $recombee->getProperties()['itemUrl']; ?>">
        <?php endif; ?>
        <?php echo $recombee->getProperties()['itemAction']; ?>
        <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>

</div>
