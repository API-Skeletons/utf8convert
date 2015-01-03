<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Database\Controller\Index' => 'Database\Controller\IndexController',
            'Database\Controller\Data' => 'Database\Controller\DataController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__.'/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            'database' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/database',
                    'defaults' => array(
                        'controller'    => 'Database\Controller\Data',
                        'action'     => 'index',
                    ),
                ),
            ),
            'iteration' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/iteration/:entity/:field/:iteration',
                    'defaults' => array(
                        'controller'    => 'Database\Controller\Data',
                        'action'     => 'iteration',
                    ),
                ),
            ),
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'validate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'validate',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'validate',
                        ),
                    ),
                ),
                'generateUtf8TableConversion' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'generate table conversion',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'generateUtf8TableConversion',
                        ),
                    ),
                ),
                'refactor' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'refactor [--whitelist=] [--blacklist=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'refactor',
                        ),
                    ),
                ),
                'convert' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'convert --supplement-has-been-ran [--whitelist=] [--blacklist=] [--clear-log]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'convert',
                        ),
                    ),
                ),
            ),
        ),
    ),
);