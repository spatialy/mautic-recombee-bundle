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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                    'recommendations' => 'mautic.plugin.recombee.form.type.recommendations',
                    'abandoned_cart'  => 'mautic.plugin.recombee.form.type.abandoned_cart',
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
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_options_type';
    }
}
