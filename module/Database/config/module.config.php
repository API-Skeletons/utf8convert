<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Database\Controller\Index' => 'Database\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__.'/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'index' => array(
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
                        'route'    => 'refactor',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'refactor',
                        ),
                    ),
                ),
                'convert' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'convert',
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