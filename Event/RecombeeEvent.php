<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticMTCPilotBundle\Entity\MTCPilot;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;

class RecombeeEvent extends CommonEvent
{
    /**
     * RecombeeEvent constructor.
     *
     * @param Recombee $entity
     * @param bool           $isNew
     */
    public function __construct(Recombee $entity, $isNew = false)
    {
        $this->entity = $entity;
        $this->isNew  = $isNew;
    }

    /**
     * @return Recombee
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param Recombee $entity
     */
    public function setEntity(Recombee $entity)
    {
        $this->entity = $entity;
    }
}
