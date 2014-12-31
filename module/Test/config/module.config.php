<?php
return array(
    'router' => array(
        'routes' => array(
            'test.rest.artists' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/artists[/:artists_id]',
                    'defaults' => array(
                        'controller' => 'Test\\V1\\Rest\\Artists\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'test.rest.artists',
        ),
    ),
    'zf-rest' => array(
        'Test\\V1\\Rest\\Artists\\Controller' => array(
            'listener' => 'Test\\V1\\Rest\\Artists\\ArtistsResource',
            'route_name' => 'test.rest.artists',
            'route_identifier_name' => 'artists_id',
            'collection_name' => 'artists',
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
            'entity_class' => 'Test\\V1\\Rest\\Artists\\ArtistsEntity',
            'collection_class' => 'Test\\V1\\Rest\\Artists\\ArtistsCollection',
            'service_name' => 'artists',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'Test\\V1\\Rest\\Artists\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'Test\\V1\\Rest\\Artists\\Controller' => array(
                0 => 'application/vnd.test.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'Test\\V1\\Rest\\Artists\\Controller' => array(
                0 => 'application/vnd.test.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'Test\\V1\\Rest\\Artists\\ArtistsEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'test.rest.artists',
                'route_identifier_name' => 'artists_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'Test\\V1\\Rest\\Artists\\ArtistsCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'test.rest.artists',
                'route_identifier_name' => 'artists_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'db-connected' => array(
            'Test\\V1\\Rest\\Artists\\ArtistsResource' => array(
                'adapter_name' => 'dbetreeorg',
                'table_name' => 'artists',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'Test\\V1\\Rest\\Artists\\Controller',
                'entity_identifier_name' => 'id',
            ),
        ),
    ),
);
