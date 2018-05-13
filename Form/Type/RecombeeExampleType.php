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
 * Class RecombeeExampleType.
 */
class RecombeeExampleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'id_to_example',
            'choice',
            [
                'choices'     => $options['choices'],
                'label'       => false,
                'label_attr'  => ['class' => 'control-label'],
                'multiple'    => false,
                'empty_value' => '',
                'attr'        => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.recombee.select.tooltip',
                    'onchange' => 'Mautic.showRecombeeExample(this.value)',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );


        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['choices']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee_example';
    }
}
