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
            'pageTemplate',
            RecombeeTemplateType::class,
            [
                'label' => 'mautic.plugin.recombee.page.template',
            ]
        );


        $builder->add(
            $builder->create(
                'emailTemplate',
                RecombeeTemplateType::class,
                [
                    'label' => 'mautic.plugin.recombee.email.template',
                ]
            )
        );



        $builder->add(
            'object',
            'choice',
            [
                'choices'     => [
                    'RecommendItemsToUser' => 'mautic.plugin.recombee.form.recommendations.items_to_user',
                    'ListUserCartAdditions' => 'mautic.plugin.recombee.form.recommendations.user_cart_additions',
                ],
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'mautic.plugin.recombee.form.recommendations.object',
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

        $builder->add(
            'type',
            'choice',
            [
                'choices'     => [
                    'items' => 'mautic.plugin.recombee.form.recommendations.items',
                    'users' => 'mautic.plugin.recombee.form.recommendations.users',
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


        $builder->add('isPublished', 'yesno_button_group');

        $builder->add(
            'buttons',
            'form_buttons'
        /*[
            'post_extra_buttons' => [
                [
                    'name'  => 'example',
                    'label' => 'mautic.plugin.recombee.example',
                    'attr'  => [
                        'class'       => 'btn btn-default btn-dnd',
                        'icon'        => 'fa fa-building',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MauticSharedModal',
                        'data-header' => $this->translator->trans('mautic.plugin.recombee.example'),
                        'href'        => $this->router->generate(
                            'mautic_recombee_action',
                            [
                                'objectId'     => $options['data']->getId(),
                                'objectAction' => 'example',
                            ]
                        ),
                    ],
                ],
            ],
        ]*/
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
