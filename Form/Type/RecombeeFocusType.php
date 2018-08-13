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

use Mautic\CoreBundle\Form\Type\SortableListType;
use MauticPlugin\MauticFocusBundle\Form\Type\FocusShowType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecombeeFocusShowType.
 */
class RecombeeFocusType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'focus',
            FocusShowType::class,
            [
                'label' => false,
                'data' => isset($options['data']['focus']) ? $options['data']['focus']: null,

            ]
        );

        if (!empty($options['urls'])) {
            $builder->add(
                'urls',
                SortableListType::class,
                [
                    'label'           => 'mautic.email.click.urls.contains',
                    'option_required' => false,
                    'with_labels'     => false,
                    'required'        => false,
                ]
            );
        }

        $builder->add(
            'type',
            RecombeeOptionsType::class,
            [
                'label' => false,
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select', 'urls']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_focus_type';
    }
}
