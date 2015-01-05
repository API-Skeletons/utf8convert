<?php

return array(
    'doctrine' => array(
        'configuration' => array(
            'orm_default' => array(
                'string_functions' => array(
                    'char_length'  => 'DoctrineExtensions\Query\Mysql\CharLength'
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Database\Controller\Index' => 'Database\Controller\IndexController',
            'Database\Controller\Data' => 'Database\Controller\DataController',
            'Database\Controller\Convert' => 'Database\Controller\ConvertController',
            'Database\Controller\Database' => 'Database\Controller\DatabaseController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__.'/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            'validate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/validate',
                    'defaults' => array(
                        'controller'    => 'Database\Controller\Data',
                        'action'     => 'validate',
                    ),
                ),
            ),
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
            'row' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/row/:entity/:primaryKey',
                    'defaults' => array(
                        'controller'    => 'Database\Controller\Data',
                        'action'     => 'row',
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
                            'controller'    => 'Database\Controller\Database',
                            'action'        => 'validateTargetDatabase',
                        ),
                    ),
                ),
                'truncate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'truncate conversion data',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Database',
                            'action'        => 'truncateUtf8ConvertDatabase',
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
                        'route'    => 'refactor --supplement-has-been-ran [--whitelist=] [--blacklist=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'refactor',
                        ),
                    ),
                ),
                'convert' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'convert [--whitelist=] [--blacklist=] [--clear-log]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Convert',
                            'action'        => 'convert',
                        ),
                    ),
                ),
                'createConversion' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'create conversion [--name=conversionName] [--whitelist=] [--blacklist=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Convert',
                            'action'        => 'createConversion',
                        ),
                    ),
                ),
                'runConversion' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'run conversion [--name=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Convert',
                            'action'        => 'runConversion',
                        ),
                    ),
                ),
                'copyConversion' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'copy conversion [--from=] [--to=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Convert',
                            'action'        => 'copyConversion',
                        ),
                    ),
                ),
                'createAdministrator' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'create-administrator [--email=] [--displayName=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'createAdministrator',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
