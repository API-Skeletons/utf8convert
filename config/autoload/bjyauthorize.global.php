<?php

return array(
    'bjyauthorize' => array(
        // Using the authentication identity provider, which basically reads the roles from the auth service's identity
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',

        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'Db\Entity\Role',
            ),
        ),


        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('administrator'), 'administration', 'access'),
                ),
#                'deny' => array(
#                ),
            ),
        ),

        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'administration' => array(),
            ),
        ),


//        'unauthorized_strategy' => 'BjyAuthorizeViewRedirectionStrategy',
        'default_role' => 'guest',

        'guards' => array(
            'BjyAuthorize\Guard\Route' => array(
                array('route' => 'home', 'roles' => array('guest')),

                array('route' => 'visualization', 'roles' => array('edit')),
                array('route' => 'visualization/conversion', 'roles' => array('edit')),
                array('route' => 'visualization/conversion/column', 'roles' => array('edit')),

                array('route' => 'database-api.rest.doctrine.data-point', 'roles' => array('edit')),
                array('route' => 'database-api.rest.data-point-data', 'roles' => array('edit')),

                array('route' => 'zfcuser', 'roles' => array('view')),
                array('route' => 'zfcuser/changepassword', 'roles' => array('view')),
                array('route' => 'zfcuser/changeemail', 'roles' => array('view')),
                array('route' => 'zfcuser/login', 'roles' => array('guest')),
                array('route' => 'zfcuser/logout', 'roles' => array('guest')),

                array('route' => 'zfcadmin', 'roles' => array('administrator')),
                array('route' => 'zfcadmin/zfcuseradmin', 'roles' => array('administrator')),
                array('route' => 'zfcadmin/zfcuseradmin/list', 'roles' => array('administrator')),
                array('route' => 'zfcadmin/zfcuseradmin/create', 'roles' => array('administrator')),
                array('route' => 'zfcadmin/zfcuseradmin/edit', 'roles' => array('administrator')),
                array('route' => 'zfcadmin/zfcuseradmin/remove', 'roles' => array('administrator')),
            ),
        ),
    ),
);