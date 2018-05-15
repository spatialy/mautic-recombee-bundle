<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Service;

use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;

class RecombeeAttr
{
    private $recombeeAttr = ['type', 'itemId', 'userId', 'limit'];

    /**
     * @return array
     */
    public function getRecombeeAttr(): array
    {
        return $this->recombeeAttr;
    }


}

