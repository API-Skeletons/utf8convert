<?php
return array(
    'zf-apigility-doctrine-query-provider' => array(
        'invokables' => array(
            'data-point-fetch-all' => 'Db\\Query\\Provider\\DataPoint\\FetchAll',
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
            'database-api.rest.data-point-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/data-point-data[/:data_point_data_id]',
                    'defaults' => array(
                        'controller' => 'DatabaseApi\\V1\\Rest\\DataPointData\\Controller',
                    ),
                ),
            ),
            'database-api.rest.doctrine.column-def' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/column[/:column_id]',
                    'defaults' => array(
                        'controller' => 'DatabaseApi\\V1\\Rest\\ColumnDef\\Controller',
                    ),
                ),
            ),
            'database-api.rpc.convert' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/convert',
                    'defaults' => array(
                        'controller' => 'DatabaseApi\\V1\\Rpc\\Convert\\Controller',
                        'action' => 'convert',
                    ),
                ),
            ),
            'database-api.rpc.url' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/url',
                    'defaults' => array(
                        'controller' => 'DatabaseApi\\V1\\Rpc\\Url\\Controller',
                        'action' => 'url',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'database-api.rest.doctrine.data-point',
            1 => 'database-api.rest.data-point-data',
            2 => 'database-api.rest.doctrine.column-def',
            3 => 'database-api.rpc.convert',
            4 => 'database-api.rpc.url',
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
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
                4 => 'POST',
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
        'DatabaseApi\\V1\\Rest\\DataPointData\\Controller' => array(
            'listener' => 'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataResource',
            'route_name' => 'database-api.rest.data-point-data',
            'route_identifier_name' => 'data_point_data_id',
            'collection_name' => 'data_point_data',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
            ),
            'collection_http_methods' => array(),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataEntity',
            'collection_class' => 'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataCollection',
            'service_name' => 'DataPointData',
        ),
        'DatabaseApi\\V1\\Rest\\ColumnDef\\Controller' => array(
            'listener' => 'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefResource',
            'route_name' => 'database-api.rest.doctrine.column-def',
            'route_identifier_name' => 'column_id',
            'entity_identifier_name' => 'id',
            'collection_name' => 'column_def',
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
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Db\\Entity\\ColumnDef',
            'collection_class' => 'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefCollection',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => 'HalJson',
            'DatabaseApi\\V1\\Rest\\DataPointData\\Controller' => 'HalJson',
            'DatabaseApi\\V1\\Rest\\ColumnDef\\Controller' => 'HalJson',
            'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => 'Json',
            'DatabaseApi\\V1\\Rpc\\Url\\Controller' => 'Json',
        ),
        'accept-whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'DatabaseApi\\V1\\Rest\\ColumnDef\\Controller' => array(
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
            'DatabaseApi\\V1\\Rest\\ColumnDef\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
            ),
        ),
        'accept_whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPointData\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/json',
                1 => 'application/*+json',
            ),
            'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ),
            'DatabaseApi\\V1\\Rpc\\Url\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ),
        ),
        'content_type_whitelist' => array(
            'DatabaseApi\\V1\\Rest\\DataPointData\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
            ),
            'DatabaseApi\\V1\\Rest\\DataPoint\\Controller' => array(
                0 => 'application/json',
            ),
            'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => array(
                0 => 'application/vnd.database-api.v1+json',
                1 => 'application/json',
            ),
            'DatabaseApi\\V1\\Rpc\\Url\\Controller' => array(
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
            'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.data-point-data',
                'route_identifier_name' => 'data_point_data_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.data-point-data',
                'route_identifier_name' => 'data_point_data_id',
                'is_collection' => true,
            ),
            'Db\\Entity\\ColumnDef' => array(
                'route_identifier_name' => 'column_id',
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.doctrine.column-def',
                'hydrator' => 'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefHydrator',
            ),
            'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'database-api.rest.doctrine.column-def',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'doctrine-connected' => array(
            'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'DatabaseApi\\V1\\Rest\\DataPoint\\DataPointHydrator',
                'query_providers' => array(
                    'fetch_all' => 'data-point-fetch-all',
                ),
            ),
            'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefResource' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'hydrator' => 'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefHydrator',
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
        'DatabaseApi\\V1\\Rest\\ColumnDef\\ColumnDefHydrator' => array(
            'entity_class' => 'Db\\Entity\\ColumnDef',
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
        'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => array(
            'input_filter' => 'DatabaseApi\\V1\\Rpc\\Convert\\Validator',
        ),
        'DatabaseApi\\V1\\Rpc\\Url\\Controller' => array(
            'input_filter' => 'DatabaseApi\\V1\\Rpc\\Url\\Validator',
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
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            5 => array(
                'name' => 'comment',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            6 => array(
                'name' => 'denied',
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
            7 => array(
                'name' => 'primaryKey',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'continue_if_empty' => true,
                'allow_empty' => false,
            ),
            8 => array(
                'name' => 'oldValue',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            9 => array(
                'name' => 'importedAt',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => false,
            ),
            10 => array(
                'name' => 'id',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'allow_empty' => true,
            ),
        ),
        'DatabaseApi\\V1\\Rpc\\Convert\\Validator' => array(),
        'DatabaseApi\\V1\\Rpc\\Url\\Validator' => array(
            0 => array(
                'name' => 'dataPointId',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataResource' => 'DatabaseApi\\V1\\Rest\\DataPointData\\DataPointDataResourceFactory',
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => 'DatabaseApi\\V1\\Rpc\\Convert\\ConvertControllerFactory',
            'DatabaseApi\\V1\\Rpc\\Url\\Controller' => 'DatabaseApi\\V1\\Rpc\\Url\\UrlControllerFactory',
        ),
    ),
    'zf-rpc' => array(
        'DatabaseApi\\V1\\Rpc\\Convert\\Controller' => array(
            'service_name' => 'Convert',
            'http_methods' => array(
                0 => 'POST',
            ),
            'route_name' => 'database-api.rpc.convert',
        ),
        'DatabaseApi\\V1\\Rpc\\Url\\Controller' => array(
            'service_name' => 'Url',
            'http_methods' => array(
                0 => 'POST',
            ),
            'route_name' => 'database-api.rpc.url',
        ),
    ),
);
