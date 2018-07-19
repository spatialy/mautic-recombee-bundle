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
        display: grid;
        grid-template-columns: repeat(12, 1fr);
    <?php if (!empty($recombee->getProperties()['background'])):    echo 'background-color:#'.$recombee->getProperties()['background'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['font'])):    echo 'font-family:'.$recombee->getProperties()['font'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['padding'])):    echo 'padding:'.$recombee->getProperties()['padding'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['style'])):    echo $recombee->getProperties()['style'];     endif; ?>;

    }

    .<?php echo $class ?> .recombee-col {
        grid-column: span <?php echo $recombee->getProperties()['columns']; ?>;
    <?php if (!empty($recombee->getProperties()['colBackground'])):    echo 'background-color:#'.$recombee->getProperties()['colBackground'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['colPadding'])):    echo 'padding:'.$recombee->getProperties()['colPadding'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['colStyle'])):    echo $recombee->getProperties()['colStyle'];     endif; ?>;

    }

    .<?php echo $class ?> .recombee-image {
        display: block;
        width: 100%;
        object: fit;
    <?php if (!empty($recombee->getProperties()['itemImageStyle'])):    echo $recombee->getProperties()['itemImageStyle'];     endif;     ?>;
    }

    .<?php echo $class ?> .recombe-short-description {
    <?php if (!empty($recombee->getProperties()['itemShortDescriptionStyle'])):    echo $recombee->getProperties()['itemShortDescriptionStyle'];     endif;     ?>;
    }

    .<?php echo $class ?> .recombee-action {
    <?php if (!empty($recombee->getProperties()['itemActionBackground'])):    echo 'background-color:#'.$recombee->getProperties()['itemActionBackground'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionColor'])):    echo 'color:#'.$recombee->getProperties()['itemActionColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionRadius'])):    echo 'border-radius:'.$recombee->getProperties()['itemActionRadius'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionPadding'])):    echo 'padding:'.$recombee->getProperties()['itemActionPadding'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionSize'])):    echo 'font-size:'.$recombee->getProperties()['itemActionSize'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemActionStyle'])):    echo $recombee->getProperties()['itemActionStyle'];     endif;     ?>;

    }

    .<?php echo $class ?> .recombee-action:hover {
    <?php if (!empty($recombee->getProperties()['itemActionHover'])):    echo 'background-color:#'.$recombee->getProperties()['itemActionHover'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-name {
    <?php if (!empty($recombee->getProperties()['itemNameColor'])):    echo 'color:#'.$recombee->getProperties()['itemNameColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemNameSize'])):    echo 'font-size:'.$recombee->getProperties()['itemNameSize'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemNamePadding'])):    echo 'padding:'.$recombee->getProperties()['itemNamePadding'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemNameStyle'])):    echo $recombee->getProperties()['itemNameStyle']; endif; ?>;
    }

    .<?php echo $class ?> .recombee-price-case {
    <?php if (!empty($recombee->getProperties()['itemPricePadding'])):    echo 'padding:'.$recombee->getProperties()['itemPricePadding'];     endif; ?>;
    }

    .<?php echo $class ?> .recombee-price {
    <?php if (!empty($recombee->getProperties()['itemPriceColor'])):    echo 'color:#'.$recombee->getProperties()['itemPriceColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemPriceSize'])):    echo 'font-size:'.$recombee->getProperties()['itemPriceSize'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemPriceBold'])):    echo 'font-weight:bold';     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemPriceStyle'])):    echo $recombee->getProperties()['itemPriceStyle'];     endif; ?>;

    }

    .<?php echo $class ?> .recombee-price-old {
    <?php if (!empty($recombee->getProperties()['itemOldPriceColor'])):    echo 'color:#'.$recombee->getProperties()['itemOldPriceColor'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemOldPriceSize'])):    echo 'font-size:'.$recombee->getProperties()['itemOldPriceSize'];     endif; ?>;
    <?php if (!empty($recombee->getProperties()['itemOldPriceStyle'])):    echo $recombee->getProperties()['itemOldPriceStyle'];     endif; ?>;
    }

</style>
<div class="recombee-global-row <?php echo $class ?>">
    <div class="recombee-row">
