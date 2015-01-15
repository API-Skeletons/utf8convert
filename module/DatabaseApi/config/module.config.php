<?php
return array(
    'zf-collection-query' => array(
        'invokables' => array(
            'data-point' => 'Db\\Query\\Provider\\DataPointQueryProvider',
        ),
    ),
    'router' => array(
        'routes' => array(
            'database-api.rest.doctrine.data-point' => array(
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
            0 => 'database-api.rest.doctrine.data-point',
        ),
    ),
    'zf-rest' => array(
        'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
            'listener' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResource',
            'route_name' => 'database-api.rest.doctrine.data-point',
            'route_identifier_name' => 'data_point_id',
            'entity_identifier_name' => 'id',
            'collection_name' => 'data_point',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(
                0 => 'query',
                1 => 'orderBy',
                2 => 'conversion',
                3 => 'column',
            ),
            'page_size' => 50,
            'page_size_param' => null,
            'entity_class' => 'Db\\Entity\\DataPoint',
            'collection_class' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointCollection',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => 'HalJson',
        ),
        'accept-whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content-type-whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'Db\\Entity\\DataPoint' => array(
                'route_identifier_name' => 'data_point_id',
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.doctrine.data-point',
                'hydrator' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointHydrator',
            ),
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.doctrine.data-point',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'doctrine-connected' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointHydrator',
                'query_provider' => 'data-point',
            ),
        ),
    ),
    'doctrine-hydrator' => array(
        'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointHydrator' => array(
            'entity_class' => 'Db\\Entity\\DataPoint',
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => true,
            'strategies' => array(),
            'use_generated_hydrator' => true,
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
                'name' => 'conversion',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Int',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            1 => array(
                'name' => 'column',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Int',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            2 => array(
                'name' => 'flagged',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            3 => array(
                'name' => 'approved',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            4 => array(
                'name' => 'newValue',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
        ),
    ),
);
