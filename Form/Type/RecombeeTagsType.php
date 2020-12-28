<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Form\Type;

use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecombeeTagsType.
 */
class RecombeeTagsType extends AbstractType
{
    /**
     * @var ApiCommands
     */
    private $apiCommands;

    /**
     * RecombeeTagsType constructor.
     *
     * @param ApiCommands $apiCommands
     */
    public function __construct(ApiCommands $apiCommands)
    {

        $this->apiCommands = $apiCommands;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                $properties =  $this->apiCommands->callCommand('ListItemProperties');
                $choices = [];
                foreach ($properties as $property) {
                    $tag = '{{ '.$property['name'].' }}';
                    $choices[$tag] = $tag;
                }

                return $choices;
            },
            'label'       => 'mautic.plugin.recombee.template.tags',
            'label_attr'  => ['class' => 'control-label'],
            'multiple'    => false,
            'required'    => false,
        ]);

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_tags';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }
}
