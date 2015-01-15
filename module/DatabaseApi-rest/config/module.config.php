<?php
return array(
    'router' => array(
        'routes' => array(
            'database-api.rest.data-point' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/data-point[/:data_point_id]',
                    'defaults' => array(
                        'controller' => 'DatabaseApi\\V1\\Rest\\DataPoint\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'database-api.rest.data-point',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResource' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResourceFactory',
        ),
    ),
    'zf-rest' => array(
        'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
            'listener' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResource',
            'route_name' => 'database-api.rest.data-point',
            'route_identifier_name' => 'data_point_id',
            'collection_name' => 'data_point',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
            ),
            'collection_query_whitelist' => array('conversion', 'column'),
            'page_size' => '50',
            'page_size_param' => null,
            'entity_class' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointEntity',
            'collection_class' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointCollection',
            'service_name' => 'DataPoint',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.data-point',
                'route_identifier_name' => 'data_point_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.data-point',
                'route_identifier_name' => 'data_point_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-content-validation' => array(
        'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
            'input_filter' => 'DatabaseApi\\V1\\Rest\\DataPoint\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'DatabaseApi\\V1\\Rest\\DataPoint\\Validator' => array(
            0 => array(
                'name' => 'approved',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            1 => array(
                'name' => 'flagged',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            2 => array(
                'name' => 'newValue',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
        ),
    ),
);
