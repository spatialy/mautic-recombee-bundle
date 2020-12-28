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
     * @var int
     */
    private $numberOfItems = 9;

    /** @var  string */
    private $templateMode;

    /** @var  string */
    private $templateType;

    /**
     * @var html
     */
    private $template;

    /**
     * @var
     */
    private $properties;

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

        $builder->createField('templateMode', 'string')
            ->columnName('template_mode')
            ->nullable()
            ->build();

        $builder->createField('templateType', 'string')
            ->columnName('template_type')
            ->nullable()
            ->build();

        $builder->createField('template', 'array')
            ->columnName('template')
            ->nullable()
            ->build();

        $builder->createField('properties', 'array')
            ->columnName('properties')
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
                'template',
                'templateMode',
                'templateType',
                'properties',
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
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->isChanged('template', $template);
        $this->template = $template;
    }

    /**
     * @param mixed $properties
     *
     * @return Recombee
     */
    public function setProperties($properties)
    {
        $this->isChanged('properties', $properties);
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * @param string $templateType
     *
     * @return Recombee
     */
    public function setTemplateType($templateType)
    {
        $this->isChanged('templateType', $templateType);
        $this->templateType = $templateType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateMode()
    {
        return $this->templateMode;
    }

    /**
     * @param string $templateMode
     *
     * @return Recombee
     */
    public function setTemplateMode($templateMode)
    {
        $this->isChanged('templateMode', $templateMode);
        $this->templateMode = $templateMode;

        return $this;
    }
}
