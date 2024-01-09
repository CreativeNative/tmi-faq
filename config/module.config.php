<?php

namespace TmiFaq;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use TmiFaq\Controller\FaqBackController;
use TmiFaq\Controller\FaqCategoryBackController;

return [
    'doctrine'      => [
        'driver' => [
            'tmi_faq'     => [
                'class' => AnnotationDriver::class,
                'cache' => 'apcu',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ]
            ],
            'orm_default' => [
                'drivers' => [
                    'TmiFaq\Entity' => 'tmi_faq',
                ]
            ]
        ]
    ],
    'controllers'   => [
        'factories' => [
            FaqBackController::class         => Controller\Factory\FaqBackControllerFactory::class,
            FaqCategoryBackController::class => Controller\Factory\FaqCategoryBackControllerFactory::class,
        ],
    ],
    'router'        => [
        'routes' => [
            'faq-back'  => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/faq-index',
                    'defaults' => [
                        'controller' => FaqBackController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'create'   => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/create',
                            'defaults' => [
                                'controller' => FaqBackController::class,
                                'action'     => 'create',
                            ],
                        ],
                    ],
                    'edit'     => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'       => '/edit[/:id][/:locale]',
                            'constraints' => [
                                'id'     => '[0-9]*',
                                'locale' => '[a-z]{2}_[A-Z]{2}',
                            ],
                            'defaults'    => [
                                'controller' => FaqBackController::class,
                                'action'     => 'edit',
                            ],
                        ],
                    ],
                    'category' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/category',
                            'defaults' => [
                                'controller' => FaqCategoryBackController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'create' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/create',
                                    'defaults' => [
                                        'controller' => FaqCategoryBackController::class,
                                        'action'     => 'create',
                                    ],
                                ],
                            ],
                            'edit'   => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit[/:id][/:locale]',
                                    'constraints' => [
                                        'id'     => '[0-9]*',
                                        'locale' => '[a-z]{2}_[A-Z]{2}',
                                    ],
                                    'defaults'    => [
                                        'controller' => FaqCategoryBackController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\FaqCategoryForm::class => Form\Factory\FaqCategoryFormFactory::class,
            Form\FaqForm::class         => Form\Factory\FaqFormFactory::class,
        ],
    ],
    'view_manager'  => [
        'template_map' => include __DIR__ . '/template_map.config.php',
    ]
];
