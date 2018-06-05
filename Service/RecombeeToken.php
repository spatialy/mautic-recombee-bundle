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
use Mautic\LeadBundle\Tracker\ContactTracker;
use Mautic\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingService;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;

class RecombeeToken
{

    private $id;

    private $itemId;

    private $userId;

    private $type;

    private $token;

    private $isToken = false;

    private $limit;

    /** @var  Recombee $entity */
    private $entity;

    /**
     * @var RecombeeModel
     */
    private $recombeeModel;

    /**
     * @var ContactTracker
     */
    private $contactTracker;

    /**
     * @var array
     */
    private $addOptions = [];


    /**
     * RecombeeToken constructor.
     *
     * @param RecombeeModel  $recombeeModel
     * @param ContactTracker $contactTracker
     */
    public function __construct(RecombeeModel $recombeeModel, ContactTracker $contactTracker)
    {
        $this->recombeeModel  = $recombeeModel;
        $this->contactTracker = $contactTracker;
    }


    public function setToken($values)
    {
        $this->setIsToken(TRUE);
        foreach ($values as $key => $value) {
            $setter = 'set'.ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }


    /**
     * @param $tokenValue
     */
    public function parseToken($tokenValue)
    {
        $tokenData = explode('|', $tokenValue);

        if (empty($tokenData['0'])) {
            return;
        }

        // first must be recombe ID
        $this->setId($tokenData['0']);
        array_shift($tokenData);

        // Then parse all optional
        $values = [];
        if (!empty($tokenData)) {
            foreach ($tokenData as $value) {
                list($key, $val) = explode("=", $value);
                $values[$key] = $val;
            }
        }
        $this->setToken($values);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        if (!$this->type && $this->entity) {
            return $this->entity->getObject();
        } else {
            if (!$this->type) {
                return 'RecommendItemsToUser';
            }
        }

        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        if (!$this->userId) {
            if ($lead = $this->contactTracker->getContact()) {
                return $lead->getId();
            }
        }

        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        if ($this->id != $id) {
            $entity = $this->recombeeModel->getEntity($id);
            if ($entity instanceof Recombee && $entity->getId()) {
                $this->setEntity($entity);
            }
        }

        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return boolean
     */
    public function isIsToken()
    {
        return $this->isToken;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        if (!$this->limit && $this->entity) {
            return $this->entity->getNumberOfItems();
        }

        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param boolean $isToken
     */
    public function setIsToken($isToken)
    {
        $this->isToken = $isToken;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getOptions($addKeys = [])
    {
        // use default set of keys
        if ($addKeys === true) {
            $addKeys = ['itemsId', 'userId', 'limit',];
        }
        $tokenOptions = [];

        foreach ($addKeys as $key) {
            $getter = 'get'.ucfirst($key);
            if (method_exists($this, $getter)) {
                $tokenOptions[$key] = $this->$getter();
            }
        }
        return array_merge($tokenOptions, $this->getAddOptions());
    }

    /**
     * @return array
     */
    public function getAddOptions()
    {
        return $this->addOptions;
    }

    /**
     * @param array $addOptions
     */
    public function setAddOptions(array $addOptions)
    {
        $this->addOptions = $addOptions;
    }


}

