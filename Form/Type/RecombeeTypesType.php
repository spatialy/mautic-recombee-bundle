<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RecombeeTypesType.
 */
class RecombeeTypesType extends AbstractType
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
                'choices' => [
                    'RecommendItemsToUser' => 'mautic.plugin.recombee.form.recommendations.items_to_user',
                    'RecommendItemsToItem' => 'mautic.plugin.recombee.form.recommendations.items_to_item',
                    'RecommendUsersToUser' => 'mautic.plugin.recombee.form.recommendations.users_to_user',
                    'RecommendUsersToItem' => 'mautic.plugin.recombee.form.recommendations.users_to_item',
                ],
                'expanded' => false,
                'multiple' => false,
                'label' => 'mautic.plugin.recombee.form.recommendations.type',
                'label_attr' => ['class' => ''],
                'empty_value' => false,
                'required' => true,
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
        return 'recombee_types';
    }
}
