<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class RecombeeOptionsType.
 */
class RecombeeOptionsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'type',
            'choice',
            [
                'choices'     => [
                    'recommend_items_to_user' => 'mautic.plugin.recombee.form.type.recommend_items_to_user',
                    'recommend_items_to_item' => 'mautic.plugin.recombee.form.type.recommend_items_to_user',
                    'abandoned_cart'  => 'mautic.plugin.recombee.form.type.abandoned_cart',
                    'advanced'        => 'mautic.plugin.recombee.form.type.advanced',
                ],
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'mautic.plugin.recombee.form.recommendations.type',
                'label_attr'  => ['class' => ''],
                'empty_value' => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );

       /* $builder->add(
            'numberOfItems',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.recombee.form.number_of_items',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.recombee.form.number_of_items.tooltip',
                ],
                'required'    => false,
                'constraints' => [
                    new Range(
                        [
                            'min' => 1,
                        ]
                    ),
                ],
            ]
        );*/

        $builder->add(
            'filter',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.form.type.filter',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'tooltip'=>'mautic.plugin.recombee.form.type.filter.tooltip',
                    'data-show-on' => '{"campaignevent_properties_type_type":["advanced"]}',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'booster',
            'text',
            [
                'label'      => 'mautic.plugin.recombee.form.type.booster',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'tooltip'=>'mautic.plugin.recombee.form.type.booster.tooltip',
                    'data-show-on' => '{"campaignevent_properties_type_type":["advanced"]}',

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
        return 'recombee_options_type';
    }
}
