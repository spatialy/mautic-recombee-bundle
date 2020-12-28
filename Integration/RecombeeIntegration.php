<?php

namespace MauticPlugin\MauticRecombeeBundle\Integration;

use Mautic\CoreBundle\Form\Type\SortableListType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\CoreBundle\Templating\Helper\AnalyticsHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticExtendeeAnalyticsBundle\Integration\EAnalyticsIntegration;
use MauticPlugin\MauticRecombeeBundle\Helper\GoogleAnalyticsHelper;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RecombeeIntegration extends AbstractIntegration
{

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'Recombee';
    }

    public function getIcon()
    {
        return 'plugins/MauticRecombeeBundle/Assets/img/recombee.png';
    }

    public function getSupportedFeatures()
    {
        return [
        ];
    }

    public function getSupportedFeatureTooltips()
    {
        return [
            //    'tracking_page_enabled' => 'mautic.integration.form.features.tracking_page_enabled.tooltip',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'keys') {

            /* @var FormBuilder $builder */
            $builder->add(
                'database',
                TextType::class,
                [
                    'label'       => 'mautic.plugin.recombee.integration.database',
                    'required'    => true,
                    'attr'        => [
                        'class' => 'form-control',
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

            $builder->add(
                'secret_key',
                TextType::class,
                [
                    'label'       => 'mautic.plugin.recombee.integration.secret_key',
                    'required'    => true,
                    'attr'        => [
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'mautic.core.value.required',
                            ]
                        ),
                        new Callback(
                            function ($validateMe, ExecutionContextInterface $context) {
                                try {

                                } catch (Ex\ApiException $e) {
                                    //$response = json_decode($e->getMessage(), true);

                                }

                                /*   if (is_array($response) && !empty($response['error'])) {
                                       $context->buildViolation($response['error'])->addViolation();
                                   }*/

                            }
                        ),
                    ],
                ]
            );
        } elseif ($formArea == 'features') {

            $builder->add(
                'currency',
                TextType::class,
                [
                    'label'      => 'mautic.plugin.recombee.form.currency',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                    ],
                ]
            );

            $builder->add(
                'abandoned_cart',
                YesNoButtonGroupType::class,
                [
                    'label' => 'mautic.plugin.recombee.abandoned_cart_reminder',
                ]
            );


            $builder->add(
                'abandoned_cart_segment',
                'leadlist_choices',
                [
                    'label'      => 'mautic.recombee.segment.abandoned.cart',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'tooltip'      => 'mautic.recombee.segment.abandoned.cart.tooltip',
                        'data-show-on' => '{"integration_details_featureSettings_abandoned_cart_1":["checked"]}',
                    ],
                    'multiple'   => false,
                    'expanded'   => false,
                ]
            );

            $builder->add(
                'abandoned_cart_order_segment',
                'leadlist_choices',
                [
                    'label'      => 'mautic.recombee.segment.abandoned.cart.order',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'tooltip'      => 'mautic.recombee.segment.abandoned.cart.order.tooltip',
                        'data-show-on' => '{"integration_details_featureSettings_abandoned_cart_1":["checked"]}',
                    ],
                    'multiple'   => false,
                    'expanded'   => false,
                ]
            );

            $builder->add(
                'abandoned_cart_order_segment_remove',
                'leadlist_choices',
                [
                    'label'      => 'mautic.recombee.segment.abandoned.cart.order.cart',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'data-show-on' => '{"integration_details_featureSettings_abandoned_cart_1":["checked"]}',
                    ],
                    'multiple'   => false,
                    'expanded'   => false,
                ]
            );
            /** @var GoogleAnalyticsHelper $analyticsHelper */
            $analyticsHelper = $this->factory->get('mautic.recombee.helper.google.analytics');
            if ($analyticsHelper->enableEAnalyticsIntegration()) {
                $builder->add(
                    'campaignGoogleAnalytics',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mautic.plugin.recombee.google.analytics.campaign',
                        'data'  => !empty($data['campaignGoogleAnalytics']) ? true : false,
                    ]
                );
            }
        }
    }
}
