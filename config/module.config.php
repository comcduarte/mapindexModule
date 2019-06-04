<?php 

use Mapindex\Controller\ConfigController;
use Mapindex\Controller\MapindexController;
use Mapindex\Controller\OwnerController;
use Mapindex\Controller\Factory\ConfigControllerFactory;
use Mapindex\Controller\Factory\MapindexControllerFactory;
use Mapindex\Controller\Factory\OwnerControllerFactory;
use Mapindex\Service\Factory\MapIndexModelPrimaryAdapterFactory;
use Mapindex\View\Helper\Functions;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'maps' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/maps',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => MapindexController::class,
                    ],
                ],
                'may_terminate' => TRUE,
                'child_routes' => [
                    'config' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/config[/:action]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => ConfigController::class,
                            ],
                        ],
                    ],
                    'import' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/import',
                            'defaults' => [
                                'action' => 'import',
                                'controller' => ConfigController::class,
                            ],
                        ],
                    ],
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => MapindexController::class,
                            ],
                        ],
                    ],
                ],
            ],
            'owners' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/owners',
                    'defaults' => [
                        'controller' => OwnerController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => TRUE,
                'child_routes' => [
                    'config' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/config',
                            'defaults' => [
                                'action' => 'config',
                                'controller' => OwnerController::class,
                            ],
                        ],
                    ],
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                    ],
                ]
            ],
        ],
    ],
    'acl' => [
        'guest' => [
        ],
        'member' => [
            'maps/default' => ['index','create','update','delete','search'],
            'owners' => ['index'],
            'owners/default' => ['index','create','update','delete','search'],
            'reports' => ['index','create','update','delete','view'],
            'reports/default' => ['index','create','update','delete','view'],
            'maps/config' => ['index','clear'],
            'maps/import' => ['import'],
        ],
        'admin' => [
            'maps/config' => ['index'],
        ],
    ],
    'controllers' => [
        'aliases' => [
            'config' => ConfigController::class,
            'mapindex' => MapindexController::class,
            'owner' => OwnerController::class,
        ],
        'factories' => [
            ConfigController::class => ConfigControllerFactory::class,
            MapindexController::class => MapindexControllerFactory::class,
            OwnerController::class => OwnerControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Mapindex\Form\MapindexForm::class => Mapindex\Form\Factory\MapindexFormFactory::class,
            Mapindex\Form\OwnerForm::class => Mapindex\Form\Factory\OwnerFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            [
                'label' => 'Maps',
                'route' => 'maps',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Add New Map',
                        'route' => 'maps/default',
                        'action' => 'create'
                    ],
                    [
                        'label' => 'Search Maps',
                        'route' => 'maps/default',
                        'action' => 'search',
                    ],
                    [
                        'label' => 'View Maps',
                        'route' => 'maps/default',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Settings',
                        'route' => 'maps/config',
                    ],
                ],
            ],
            [
                'label' => 'Owners',
                'route' => 'owners',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Add New Owner',
                        'route' => 'owners/default',
                        'action' => 'create',
                    ],
                    [
                        'label' => 'Search Owners',
                        'route' => 'owners/default',
                        'action' => 'search',
                    ],
                    [
                        'label' => 'View Owners',
                        'route' => 'owners',
                    ],
                    [
                        'label' => 'Settings',
                        'route' => 'owners/config',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'mapindex-model-primary-adapter-config' => 'model-primary-adapter-config',
        ],
        'factories' => [
            'mapindex-model-primary-adapter' => MapIndexModelPrimaryAdapterFactory::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'functions' => Functions::class,
        ],
        'factories' => [
            Functions::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];