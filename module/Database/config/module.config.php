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
            'index' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/database',
                    'defaults' => array(
                        'controller'    => 'Database\Controller\Index',
                        'action'        => 'index',
                    ),
                ),
            ),
            'page' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/page',
                    'defaults' => array(
                        'controller'    => 'ContentManagement\Controller\Page',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'detail' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:url-identifier',
                            'defaults' => array(
                                'controller'    => 'ContentManagement\Controller\Page',
                                'action'        => 'detail',
                            ),
                            'constraints' => array(
#                                'url-identifier' => '^(!create)|(!edit)$',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);