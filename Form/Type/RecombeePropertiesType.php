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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class RecombeePropertiesType.
 */
class RecombeePropertiesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'columns',
            'choice',
            [
                'choices' => [
                    '2' => '6',
                    '3' => '4',
                    '4' => '3',
                    '6' => '2',
                    '12' => '1',
                ],
                'expanded' => false,
                'multiple' => false,
                'label' => 'mautic.recombee.form.columns',
                'label_attr' => ['class' => ''],
                'empty_value' => false,
                'required' => true,
                'data'        => isset($options['data']['columns']) ? $options['data']['columns'] : 3,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );


        $builder->add(
            'background',
            'text',
            [
                'label'      => 'mautic.recombee.form.background.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemName',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemImage',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.image',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemShortDescription',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.short.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemUrl',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'action',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.item.action',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemPrice',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.price',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemOldPrice',
            RecombeeTagsType::class,
            [
                'label'      => 'mautic.plugin.recombee.item.old.price',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    //'onchange'    => 'Mautic.recombeeUpdatePreview()',
                ],
                'required'   => false,
            ]
        );


    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_properties';
    }
}
