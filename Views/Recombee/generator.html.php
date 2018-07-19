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

<style>

    .<?php echo $class ?> .recombee-row {
    <?php if (!empty($recombee->getProperties()['font'])):    echo 'font-family:'.$recombee->getProperties()['font'];     endif; ?>

    }

    .<?php echo $class ?> .recombee-row {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
    }

    .<?php echo $class ?> .recombee-col {
        grid-column: span <?php echo $recombee->getProperties()['columns']; ?>;
    <?php if (!empty($recombee->getProperties()['padding'])):    echo 'padding:'.$recombee->getProperties()['padding'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-image {
        display: block;
        width: 100%;
        object: fit;
    }

    .<?php echo $class ?> .recombee-action {
    <?php if (!empty($recombee->getProperties()['itemActionBackground'])):    echo 'background-color:#'.$recombee->getProperties()['itemActionBackground'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionColor'])):    echo 'color:#'.$recombee->getProperties()['itemActionColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionRadius'])):    echo 'border-radius:'.$recombee->getProperties()['itemActionRadius'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionPadding'])):    echo 'padding:'.$recombee->getProperties()['itemActionPadding'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionSize'])):    echo 'font-size:'.$recombee->getProperties()['itemActionSize'];     endif; ?>;

    }

    .<?php echo $class ?> .recombee-action:hover {
    <?php if (!empty($recombee->getProperties()['itemActionHover'])):    echo 'background-color:#'.$recombee->getProperties()['itemActionHover'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-name {
    <?php if (!empty($recombee->getProperties()['itemNameColor'])):    echo 'color:#'.$recombee->getProperties()['itemNameColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemNameSize'])):    echo 'font-size:'.$recombee->getProperties()['itemNameSize'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemNamePadding'])):    echo 'padding:'.$recombee->getProperties()['itemNamePadding'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-price-case {
    <?php if (!empty($recombee->getProperties()['itemPricePadding'])):    echo 'padding:'.$recombee->getProperties()['itemPricePadding'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-price {
    <?php if (!empty($recombee->getProperties()['itemPriceColor'])):    echo 'color:#'.$recombee->getProperties()['itemPriceColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemPriceSize'])):    echo 'font-size:'.$recombee->getProperties()['itemPriceSize'];     endif; ?>;
    }

</style>
<div class="recombee-global-row <?php echo $class ?>">
    <div class="recombee-row">
        <?php for ($i = 0; $i < $recombee->getNumberOfItems(); $i++): ?>
            <div class="recombee-col">
                <?php if (!empty($recombee->getProperties()['itemImage'])): ?>
                    <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
                        <a  href="<?php echo $recombee->getProperties()['itemUrl']; ?>">
                    <?php endif; ?>
                    <img class="recombee-image" src="http://via.placeholder.com/350" alt="">
                    <?php if (!empty($recombee->getProperties()['itemUrl'])): ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($recombee->getProperties()['itemName'])): ?>
                    <h5 class="recombee-name"><?php echo $recombee->getProperties()['itemName']; ?></h5>
                <?php endif; ?>
                <?php if (!empty($recombee->getProperties()['itemShortDescription'])): ?>
                    <p class="recombe-short-description"><?php echo $recombee->getProperties(
                        )['itemShortDescription']; ?></p>
                <?php endif; ?>
                <?php if (!empty($recombee->getProperties()['itemPrice'])): ?>
                    <p class="recombee-price-case">
                        <span class="recombee-price"><?php echo $recombee->getProperties()['itemPrice']; ?></span>
                        <?php if (!empty($recombee->getProperties()['itemOldPrice'])): ?>
                            <span style="text-decoration: line-through"><?php echo $recombee->getProperties(
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
        <?php endfor; ?>
    </div>
</div>
