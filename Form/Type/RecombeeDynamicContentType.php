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

use Mautic\DynamicContentBundle\Form\Type\DynamicContentListType;
use Mautic\DynamicContentBundle\Form\Type\DynamicContentSendType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RecombeeDynamicContentType.
 */
class RecombeeDynamicContentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $builder->add(
            'dynamic_content',
            'dwc_list',
            [
                'label'      => 'mautic.dynamicContent.send.selectDynamicContents',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.dynamicContent.choose.dynamicContents',
                    'onchange' => 'Mautic.disabledDynamicContentAction()',
                ],
                'where'       => 'e.isCampaignBased = 1', // do not show dwc with filters
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(['message' => 'mautic.core.value.required']),
                ],
            ]
        );

        $builder->add(
            'slot',
            'text',
            [
                'label'      => 'mautic.dynamicContent.label.slot_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.dynamicContent.send.slot_name.tooltip',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(['message' => 'mautic.core.value.required']),
                ],
            ]
        );

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
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_dynamic_content_type';
    }
}
