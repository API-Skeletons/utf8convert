<?php

namespace Database;

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
            'Database\Controller\Database' => 'Database\Controller\DatabaseController',
        ),
        'factories' => [
            Controller\ValidateController::class => Controller\ValidateControllerFactory::class,
            Controller\TruncateController::class => Controller\TruncateControllerFactory::class,
            Controller\GenerateTableSqlController::class => Controller\GenerateTableSqlControllerFactory::class,
            Controller\ConversionController::class => Controller\ConversionControllerFactory::class,
        ],
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__.'/../view',
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'countDataPoint' => View\Helper\CountDataPointFactory::class,
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'database-validate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'database:validate',
                        'defaults' => array(
                            'controller'    => Controller\ValidateController::class,
                            'action'        => 'validate',
                        ),
                    ),
                ),
                'database-truncate' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'database:truncate',
                        'defaults' => array(
                            'controller'    => Controller\TruncateController::class,
                            'action'        => 'truncateUtf8ConvertDatabase',
                        ),
                    ),
                ),
                'conversion-create' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:create [--name=conversionName] [--whitelist=] [--blacklist=]',
                        'defaults' => array(
                            'controller'    => Controller\ConversionController::class,
                            'action'        => 'create',
                        ),
                    ),
                ),
                'conversion-convert' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:convert --name= [--force]',
                        'defaults' => array(
                            'controller'    => Controller\ConversionController::class,
                            'action'        => 'convert',
                        ),
                    ),
                ),
                'conversion-export' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:export --name=conversionName',
                        'defaults' => array(
                            'controller'    => Controller\ConversionController::class,
                            'action'        => 'export',
                        ),
                    ),
                ),
                'conversion-clone' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'conversion:clone [--from=] [--to=]',
                        'defaults' => array(
                            'controller'    => Controller\ConversionController::class,
                            'action'        => 'clone',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
