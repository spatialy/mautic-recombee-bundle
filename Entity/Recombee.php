<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Recombee
 * @package MauticPlugin\MauticRecombeeBundle\Entity
 */
class Recombee extends FormEntity
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;


    /**
     * @var \DateTime
     */
    private $publishUp;

    /**
     * @var \DateTime
     */
    private $publishDown;

    /**
     * @var string
     */
    private $filter;

    /**
     * @var string
     */
    private $boost;

    /**
     * @var int
     */
    private $numberOfItems = 9;

    /**
     * @var string
     */
    private $pageTemplate;


    /**
     * @var string
     */
    private $emailTemplate;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $object;

    /**
     * Recombee constructor.
     */
    public function __construct()
    {
    }

    /**
     * Clone method.
     */
    public function __clone()
    {
        $this->id              = null;

        parent::__clone();
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('recombee')
            ->setCustomRepositoryClass('MauticPlugin\MauticRecombeeBundle\Entity\RecombeeRepository');

        $builder->addIdColumns('name', '');

        $builder->addPublishDates();

        $builder->createField('numberOfItems', Type::INTEGER)
            ->columnName('number_of_items')
            ->nullable()
            ->build();

        $builder->createField('filter', 'text')
            ->nullable()
            ->build();

        $builder->createField('boost', 'text')
            ->nullable()
            ->build();

        $builder->createField('pageTemplate', 'array')
            ->columnName('page_template')
            ->nullable()
            ->build();

        $builder->createField('emailTemplate', 'array')
            ->columnName('email_template')
            ->nullable()
            ->build();

        $builder->createField('type', 'text')
            ->columnName('type')
            ->nullable()
            ->build();

        $builder->createField('object', 'text')
            ->columnName('object')
            ->nullable()
            ->build();

    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetaData(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank(['message' => 'mautic.core.name.required']));
        $metadata->addPropertyConstraint('numberOfItems', new NotBlank(['message' => 'mautic.core.name.required']));
    }

    /**
     * @param ApiMetadataDriver $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('recombee')
            ->addListProperties([
                'id',
                'name',
                'numberOfItems',
            ])
            ->addProperties([
                'publishUp',
                'publishDown',
                'filter',
                'boost',
                'boost',
                'pageTemplate',
                'htmlTemplate',
            ])
            ->build();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getPublishUp()
    {
        return $this->publishUp;
    }

    /**
     * @param \DateTime $publishUp
     *
     * @return $this
     */
    public function setPublishUp($publishUp)
    {
        $this->isChanged('publishUp', $publishUp);
        $this->publishUp = $publishUp;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDown()
    {
        return $this->publishDown;
    }

    /**
     * @param \DateTime $publishDown
     *
     * @return $this
     */
    public function setPublishDown($publishDown)
    {
        $this->isChanged('publishDown', $publishDown);
        $this->publishDown = $publishDown;

        return $this;
    }

    /**
     * @return string
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @param string $boost
     */
    public function setBoost($boost)
    {
        $this->isChanged('boost', $boost);
        $this->boost = $boost;
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->isChanged('filter', $filter);
        $this->filter = $filter;
    }

    /**
     * @return int
     */
    public function getNumberOfItems()
    {
        return $this->numberOfItems;
    }

    /**
     * @param int $numberOfItems
     */
    public function setNumberOfItems($numberOfItems)
    {
        $this->isChanged('numberOfItems', $numberOfItems);
        $this->numberOfItems = $numberOfItems;
    }

    /**
     * @return string
     */
    public function getPageTemplate()
    {
        return $this->pageTemplate;
    }

    /**
     * @param string $pageTemplate
     */
    public function setPageTemplate($pageTemplate)
    {
        $this->isChanged('pageTemplate', $pageTemplate);
        $this->pageTemplate = $pageTemplate;
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @param string $emailTemplate
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->isChanged('emailTemplate', $emailTemplate);
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->isChanged('type', $type);
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->isChanged('object', $object);
        $this->object = $object;
    }
}
