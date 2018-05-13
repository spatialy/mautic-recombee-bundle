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

class RecombeeToken
{
    private $recombeeType = 'RecommendItemsToUser';

    private $recombeeId;

    private $recombeeItemId;

    private $recombeeUserId;

    private $recombeeToken;

    private $isToken = false;

    private $limit;

    public function setToken($tokenValue)
    {
        $tokenData = explode('|', $tokenValue);

        if (empty($tokenData['0'])) {
            return;
        }
        $this->setIsToken(true);

        // first must be recombe ID
        $this->setRecombeeId($tokenData['0']);
        array_shift($tokenData);

        // Then parse all optional
        if (!empty($tokenData)) {
            foreach ($tokenData as $value) {
                list($key, $val) = explode("=", $value);
                switch ($key) {
                    case "type":
                        $this->setRecombeeType($val);
                        break;
                    case "user-id":
                        $this->setRecombeeUserId($val);
                        break;
                    case "item-id":
                        $this->setRecombeeItemId($val);
                        break;
                    case "limit":
                        $this->setLimit($val);
                        break;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getRecombeeId()
    {
        return $this->recombeeId;
    }

    /**
     * @param mixed $recombeeId
     */
    public function setRecombeeId($recombeeId)
    {
        $this->recombeeId = $recombeeId;
    }

    /**
     * @return string
     */
    public function getRecombeeType(): string
    {
        return $this->recombeeType;
    }

    /**
     * @param string $recombeeType
     */
    public function setRecombeeType(string $recombeeType)
    {
        $this->recombeeType = $recombeeType;
    }

    /**
     * @return mixed
     */
    public function getRecombeeItemId()
    {
        return $this->recombeeItemId;
    }

    /**
     * @param mixed $recombeeItemId
     */
    public function setRecombeeItemId($recombeeItemId)
    {
        $this->recombeeItemId = $recombeeItemId;
    }

    /**
     * @return mixed
     */
    public function getRecombeeUserId()
    {
        return $this->recombeeUserId;
    }

    /**
     * @param mixed $recombeeUserId
     */
    public function setRecombeeUserId($recombeeUserId)
    {
        $this->recombeeUserId = $recombeeUserId;
    }

    /**
     * @return mixed
     */
    public function getRecombeeToken()
    {
        return $this->recombeeToken;
    }

    /**
     * @param mixed $recombeeToken
     */
    public function setRecombeeToken($recombeeToken)
    {
        $this->recombeeToken = $recombeeToken;
    }

    /**
     * @return boolean
     */
    public function isIsToken(): bool
    {
        return $this->isToken;
    }

    /**
     * @param boolean $isToken
     */
    public function setIsToken(bool $isToken)
    {
        $this->isToken = $isToken;
    }

    public function setDefaultFromEntity(Recombee $entity, Lead $lead)
    {
        if (!$this->getRecombeeType()) {
            $this->setRecombeeType($entity->getRecommendationsType());
        }

        if (!$this->getLimit()) {
            $this->setLimit($entity->getNumberOfItems());
        }

        if (!$this->getRecombeeUserId()) {
            $this->setRecombeeUserId($lead->getId());
        }
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

}

