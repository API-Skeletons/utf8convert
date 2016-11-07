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
            'Database\Controller\Conversion' => 'Database\Controller\ConversionController',
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
                        'action'     => 'database:validate',
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
                'administrator-create' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'administrator:create [--email=] [--displayName=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'administratorCreate',
                        ),
                    ),
                ),
                'database-validate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'database:validate',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Database',
                            'action'        => 'databaseValidate',
                        ),
                    ),
                ),
                'database-generate-utf8tables' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'database:generate:utf8-tables',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Index',
                            'action'        => 'databaseGenerateUtf8Tables',
                        ),
                    ),
                ),
                'database-truncate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'database:truncate',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Database',
                            'action'        => 'truncateUtf8ConvertDatabase',
                        ),
                    ),
                ),
                'conversion-create' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:create [--name=conversionName] [--whitelist=] [--blacklist=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Conversion',
                            'action'        => 'create',
                        ),
                    ),
                ),
                'conversion-convert' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:convert --name=',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Conversion',
                            'action'        => 'convert',
                        ),
                    ),
                ),
                'conversion-export' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:export --name=conversionName',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Conversion',
                            'action'        => 'export',
                        ),
                    ),
                ),
                'conversion-clone' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:clone [--from=] [--to=]',
                        'defaults' => array(
                            'controller'    => 'Database\Controller\Conversion',
                            'action'        => 'clone',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
