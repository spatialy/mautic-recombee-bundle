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
            'font',
            'choice',
            [
                'choices' => [
                    'Arial, Helvetica, sans-serif'                             => 'Arial',
                    '\'Arial Black\', Gadget, sans-serif'                      => 'Arial Black',
                    '\'Arial Narrow\', sans-serif'                             => 'Arial Narrow',
                    'Century Gothic, sans-serif'                               => 'Century Gothic',
                    'Copperplate / Copperplate Gothic Light, sans-serif'       => 'Copperplate Gothic Light',
                    '\'Courier New\', Courier, monospace'                      => 'Courier New',
                    'Georgia, Serif'                                           => 'Georgia',
                    'Impact, Charcoal, sans-serif'                             => 'Impact',
                    '\'Lucida Console\', Monaco, monospace'                    => 'Lucida Console',
                    '\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'   => 'Lucida Sans Unicode',
                    '\'Palatino Linotype\', \'Book Antiqua\', Palatino, serif' => 'Palatino',
                    'Tahoma, Geneva, sans-serif'                               => 'Tahoma',
                    '\'Times New Roman\', Times, serif'                        => 'Times New Roman',
                    '\'Trebuchet MS\', Helvetica, sans-serif'                  => 'Trebuchet MS',
                    'Verdana, Geneva, sans-serif'                              => 'Verdana',
                ],
                'label'      => 'mautic.focus.form.font',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required'    => false,
                'empty_value' => false,
            ]
        );

        $builder->add(
            'padding',
            'text',
            [
                'label'      => 'mautic.recombee.form.padding',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'style',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'colBackground',
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
            'colPadding',
            'text',
            [
                'label'      => 'mautic.recombee.form.padding',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'colStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
            'itemNameColor',
            'text',
            [
                'label'      => 'mautic.recombee.form.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemNameSize',
            'text',
            [
                'label'      => 'mautic.recombee.form.font.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemNamePadding',
            'text',
            [
                'label'      => 'mautic.recombee.form.padding',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemNameStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
            'itemImageStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
            'itemShortDescriptionStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
            'itemAction',
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
            'itemActionBackground',
            'text',
            [
                'label'      => 'mautic.recombee.form.background.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemActionHover',
            'text',
            [
                'label'      => 'mautic.recombee.form.background.hover.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemActionColor',
            'text',
            [
                'label'      => 'mautic.recombee.form.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemActionPadding',
            'text',
            [
                'label'      => 'mautic.recombee.form.padding',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemActionRadius',
            'text',
            [
                'label'      => 'mautic.recombee.form.radius',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemActionSize',
            'text',
            [
                'label'      => 'mautic.recombee.form.font.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemActionStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
            'itemPriceColor',
            'text',
            [
                'label'      => 'mautic.recombee.form.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemPricePadding',
            'text',
            [
                'label'      => 'mautic.recombee.form.padding',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemPriceSize',
            'text',
            [
                'label'      => 'mautic.recombee.form.font.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemPriceBold',
            'yesno_button_group',
            [
                'label' => 'mautic.plugin.recombee.bold',
                'attr'  => [
                ],
                'data'        => isset($options['data']['itemPriceBold']) ? :false,

            ]
        );

        $builder->add(
            'itemPriceStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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


        $builder->add(
            'itemOldPriceColor',
            'text',
            [
                'label'      => 'mautic.recombee.form.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );


        $builder->add(
            'itemOldPriceSize',
            'text',
            [
                'label'      => 'mautic.recombee.form.font.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'itemOldPriceStyle',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.style',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
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
