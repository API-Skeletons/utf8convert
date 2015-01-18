[![Build status](https://api.travis-ci.org/phpro/zf-doctrine-hydration-module.svg)](http://travis-ci.org/phpro/zf-doctrine-hydration-module)

# Doctrine Hydration Module
This module provides a configurable way to create new doctrine hydrators.
By using the configurable API, it is easy to create a custom hydrator for any use case you want.

For ORM, the basic hydrator from the doctrine module is being used. It is possible to configure your own strategies for complex objects like referenced entities.

For MongoDB ODM, a specific hydrator is added. This hydrator will be able to handle Referenced documents and Embedded Documents.
It is also possible to hydrate advanced documents with discriminator maps.

#Installation

## Add to composer.json
```
"phpro/doctrine-hydration-module": "dev-master"
```

## Add to application config
```php
return array(
    'modules' => array(
        'Phpro\\DoctrineHydrationModule',
        // other libs...
    ),
    // Other config
);
```

### Hydrator configuration
```php
return array(
    'doctrine-hydrator' => array(
        'hydrator-manager-key' => array(
            'entity_class' => 'App\Entity\EntityClass',
            'object_manager' => 'doctrine.objectmanager.key.in.servicelocator',
            'by_value' => true,
            'use_generated_hydrator' => true,
            'naming_strategy' => 'custom.naming.strategy.key.in.servicemanager',
            'hydrator' => 'custom.hydrator.key.in.hydratormanager',
            'strategies' => array(
                'fieldname' => 'custom.strategy.key.in.servicemanager',
            ),
            'filters' => array(
                'custom_filter_name' => array(
                    'condition' => 'and', // optional, default is 'or'
                    'filter'    => 'custom.hydrator.filter.key.in.servicemanager',
                ),
            ),
        ),
    ),
);
```

use_generated_hydrator will only be used with mongoDB ODM and will use the generated hydrators instead of the Doctrine Module Hydrator.
Strategies will not work when this option is set to `true`.


From here on, you can get the hydrator by calling `get('hydrator-manager-key')` on the HydratorManager.

# Custom strategies:

## MongoDB ODM

- DateTimeField: Used for DateTime objects
- DefaultRelation: Leave the relation AS-IS. Does not perform any modifications on the field.
- EmbeddedCollection: Used for embedded collections
- EmbeddedField: Used for embedded fields
- ReferencedCollection: Used for referenced collections
- ReferencedField: Used for referenced fields.
- EmbeddedReferenceCollection: This is a custom strategy that can be used in an API to display all fields in a referenced object. The hydration works as a regular referenced object.
- EmbeddedReferenceField: This is a custom strategy that can be used in an API to display all fields in a referenced object. The hydration works as a regular referenced object.

# Custom Filters

Custom filters allow you to fine-tune the results of the hydrator's `extract` functionality by determining which fields should be extracted. 

To configure custom filters:
```php
return array(
    'doctrine-hydrator' => array(
        'custom-hydrator' => array(
            // other config
            'filters' => array(
                'custom.filter.name' => array(
                    'condition' => 'and', //optional, default: FilterComposite::CONDITION_OR,
                    'filter' => 'custom.filter', // a name in the Service Manager
                ),
            ),
        ),
    ),
);

```
In this example configuration, the hydrator factory will retrieve `custom.filter` from the Service Manager and inject it as a filter into the hydrator. The filter must implement `Zend\Stdlib\Hydrator\Filter\FilterInterface`. 

The service's `filter($fieldName)` function will be called by the hydrator during `extract` and the field name being extracted will be passed as an argument. The `filter()` function must return a truthy value: if `true` then the field will NOT be extracted.


# Override hydrator:

If the standard DoctrineHydrator is not flexible enough, you can set a custom `hydrator`. This allows you to use an extended DoctrineHydrator or another existing hydrator, and configure it with this module. This setting will override `use_generated_hydrator`.

```php
return array(
    'doctrine-hydrator' => array(
        'custom-hydrator' => array(
            // other config
            'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable'
        ),
    ),
);
```

# Testing
This package is fully tested with Cs fixer and PhpUnit. The MongoDB tests require mongodb to be installed on your machine. You can skip these tests by adding a testsuite to the command:
```sh
# Php coding standards:
# (The files are loaded according to the PSR-4 standard. The PSR-0 fix will fail!)
./vendor/bin/php-cs-fixer fix . --dry-run  --fixers=-psr0

# Phpunit:
./vendor/bin/phpunit

# Testing one testsuite:
./vendor/bin/phpunit --testsuite="Main"
./vendor/bin/phpunit --testsuite="ODM"
```
