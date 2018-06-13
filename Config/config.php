<?php

return [
    'name'        => 'Recombee',
    'description' => 'Enable integration with Recombee  - personalize content using Recommender as a Service',
    'author'      => 'kuzmany.biz',
    'version'     => '0.9.0',
    'services'    => [
        'events'       => [
            'mautic.recombee.pagebundle.subscriber'  => [
                'class'     => MauticPlugin\MauticRecombeeBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'mautic.recombee.helper',
                    'mautic.recombee.service.replacer',
                    'mautic.recombee.service.api.commands',
                    'mautic.recombee.service.token.html.replacer',
                ],
            ],
            'mautic.recombee.campaignbundle.subscriber'  => [
                'class'     => MauticPlugin\MauticRecombeeBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.email.model.email',
                    'mautic.campaign.model.event',
                    'mautic.channel.model.queue',
                    'mautic.email.model.send_email_to_user',
                    'mautic.recombee.service.replacer',
                    'mautic.campaign.model.campaign',
                ],
            ],
            'mautic.recombee.leadbundle.subscriber'  => [
                'class'     => MauticPlugin\MauticRecombeeBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'mautic.recombee.helper',
                    'mautic.recombee.service.api.commands',
                ],
            ],
            'mautic.recombee.emailbundle.subscriber' => [
                'class'     => MauticPlugin\MauticRecombeeBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'mautic.recombee.helper',
                    'mautic.recombee.service.replacer',
                ],
            ],
            'mautic.recombee.buildjs.subscriber'     => [
                'class'     => MauticPlugin\MauticRecombeeBundle\EventListener\BuildJsSubscriber::class,
                'arguments' => [
                ],
            ],
        ],
        'models'       => [
            'mautic.recombee.model.recombee' => [
                'class' => MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel::class,
            ],
        ],
        'forms'        => [
            'mautic.form.type.recombee'         => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeType::class,
                'alias'     => 'recombee',
                'arguments' => [
                    'mautic.security',
                    'router',
                    'translator',
                ],
            ],
            'mautic.form.type.recombee.types'             => [
                'class' => MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeTypesType::class,
                'alias' => 'recombee_types',
            ],
            'mautic.form.type.recombee.recombee_template' => [
                'class' => MauticPlugin\MauticRecombeeBundle\Form\Type\RecombeeTemplateType::class,
                'alias' => 'recombee_template',
            ],
        ],
        'other'        => [
            'mautic.recombee.helper'                      => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Helper\RecombeeHelper::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.recombee.model.recombee',
                    'translator',
                    'mautic.security',
                ],
            ],
            'mautic.recombee.api.recombee'                => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Api\RecombeeApi::class,
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                    'mautic.helper.template.version',
                ],
            ],
            'mautic.recombee.service.api.commands'        => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands::class,
                'arguments' => [
                    'mautic.recombee.api.recombee',
                    'monolog.logger.mautic',
                    'translator',
                    'mautic.recombee.service.api.segment.mapping',
                    'mautic.recombee.service.token.finder',
                ],
            ],
            'mautic.recombee.service.api.segment.mapping' => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Api\Service\SegmentMapping::class,
                'arguments' => [
                    'mautic.lead.model.list',
                    'mautic.helper.integration',
                ],
            ],
            'mautic.recombee.service.token'               => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken::class,
                'arguments' => [
                    'mautic.recombee.model.recombee',
                    'mautic.lead.model.lead',
                    'mautic.campaign.model.campaign',
                ],
            ],
            'mautic.recombee.service.token.finder'        => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenFinder::class,
                'arguments' => [
                    'mautic.recombee.service.token',
                ],
            ],
            'mautic.recombee.service.replacer'            => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer::class,
                'arguments' => [
                    'mautic.recombee.service.token',
                    'mautic.recombee.service.token.finder',
                    'mautic.recombee.service.token.generator',
                ],
            ],
            'mautic.recombee.service.token.generator'     => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Service\RecombeeGenerator::class,
                'arguments' => [
                    'mautic.recombee.model.recombee',
                    'mautic.recombee.api.recombee',
                    'mautic.lead.model.lead',
                    'twig',
                    'mautic.recombee.service.api.commands'
                ],
            ],
            'mautic.recombee.service.token.html.replacer' => [
                'class'     => MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenHTMLReplacer::class,
                'arguments' => [
                    'mautic.recombee.service.token.generator',
                    'mautic.recombee.service.token',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.recombee' => [
                'class'     => \MauticPlugin\MauticRecombeeBundle\Integration\RecombeeIntegration::class,
                'arguments' => [
                ],
            ],
        ],
    ],
    'routes'      => [
        'main'   => [
            'mautic_recombee_index'  => [
                'path'       => '/recombee/{page}',
                'controller' => 'MauticRecombeeBundle:Recombee:index',
            ],
            'mautic_recombee_action' => [
                'path'       => '/recombee/{objectAction}/{objectId}',
                'controller' => 'MauticRecombeeBundle:Recombee:execute',
            ],
        ],
        'public' => [
            'mautic_recombee_api_content' => [
                'path'       => '/recombee/dwc',
                'controller' => 'MauticRecombeeBundle:Ajax:get',
            ],
            'mautic_recombee_tests' => [
                'path'       => '/recombee/tests',
                'controller' => 'MauticRecombeeBundle:Tests:run',
            ],
        ],
        'api'    => [
            'mautic_recombee_api' => [
                'path'       => '/recombee/{component}',
                'controller' => 'MauticRecombeeBundle:Api\RecombeeApi:process',
                'method'     => 'POST',
            ],
        ],
    ],
    'menu'        => [
        'main' => [
            'items' => [
                'mautic.plugin.recombee' => [
                    'route'    => 'mautic_recombee_index',
                    'access'   => ['recombee:recombee:viewown', 'recombee:recombee:viewother'],
                    'checks'   => [
                        'integration' => [
                            'Recombee' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'parent'   => 'mautic.core.components',
                    'priority' => 100,
                ],
            ],
        ],
    ],
    'parameters'  => [],
];
