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
<?php if ($preview) {
    echo html_entity_decode($recombee->getProperties()['footer']);
    ?>
<?php } else {
    echo $recombee->getProperties()['footer']; ?>
<?php } ?>
    </div>
</div>
