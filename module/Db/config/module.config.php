<?php

namespace Db;

return array(
    'service_manager' => array(
        'aliases' => array(
            # 'zfcuser_doctrine_em' => 'doctrine.entitymanager.orm_default',
            'zfcuser_doctrine_em' => 'Doctrine\ORM\EntityManager',
        ),
    ),

    'data-fixture' => array(
        'Db_fixture' => __DIR__.'/../src/Db/Fixture',
    ),

    'doctrine' => array(
        'driver' => array(
            'zfcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => array(__DIR__.'/xml'),
            ),
           'orm_default' => array(
                'class'   => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => array(
                    __NAMESPACE__.'\Entity' => 'zfcuser_entity',
                ),
            ),
        ),
    ),
);
