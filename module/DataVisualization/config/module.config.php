<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'DataVisualization\Controller\Index' => 'DataVisualization\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__.'/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'countDataPoint' => 'DataVisualization\View\Helper\CountDataPoint',
            'countDataPointApproved' => 'DataVisualization\View\Helper\CountDataPointApproved',
            'countDataPointFlagged' => 'DataVisualization\View\Helper\CountDataPointFlagged',
            'countDataPointDenied' => 'DataVisualization\View\Helper\CountDataPointDenied',
            'countDataPointNotReviewed' => 'DataVisualization\View\Helper\CountDataPointNotReviewed',
        ),
    ),
    'router' => array(
        'routes' => array(
            'visualization' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/visualization',
                    'defaults' => array(
                        'controller'    => 'DataVisualization\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'conversion' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/conversion/:conversion_id',
                            'defaults' => array(
                                'controller'    => 'DataVisualization\Controller\Index',
                                'action'     => 'conversion',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'table' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/table/:table_id',
                                    'defaults' => array(
                                        'controller'    => 'DataVisualization\Controller\Index',
                                        'action'     => 'table',
                                    ),
                                ),
                            ),
                            'column' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/column/:column_id',
                                    'defaults' => array(
                                        'controller'    => 'DataVisualization\Controller\Index',
                                        'action'     => 'column',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
