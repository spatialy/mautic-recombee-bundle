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

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class RecombeeType.
 */
class RecombeeType extends AbstractType
{
    /**
     * @var \Mautic\CoreBundle\Security\Permissions\CorePermissions
     */
    protected $security;


    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * CompanyType constructor.
     *
     * @param CorePermissions $security
     */
    public function __construct(CorePermissions $security, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->security   = $security;
        $this->router     = $router;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'numberOfItems',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.recombee.form.number_of_items',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.recombee.form.number_of_items.tooltip',
                ],
                'required'    => true,
                'data'        => $options['data']->getNumberOfItems(),
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                    new Range(
                        [
                            'min' => 1,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'filter',
            'textarea',
            [
                'label'    => 'mautic.plugin.recombee.form.filter',
                'required' => false,
                'attr'     => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.recombee.form.filter.tooltip',

                ],
            ]
        );

        $builder->add(
            'boost',
            'textarea',
            [
                'label'    => 'mautic.plugin.recombee.form.boost',
                'required' => false,
                'attr'     => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.recombee.form.boost.tooltip',
                ],
            ]
        );

        $builder->add(
            'template',
            RecombeeTemplateType::class,
            [
                'label' => 'mautic.plugin.recombee.template',
            ]
        );



        $builder->add('isPublished', 'yesno_button_group');

        $builder->add(
            'buttons',
            'form_buttons'
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
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recombee';
    }
}
