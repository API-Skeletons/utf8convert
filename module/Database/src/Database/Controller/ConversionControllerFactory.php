<?php

namespace Database\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use DoctrineModule\Authentication\Adapter\ObjectRepository;

final class ConversionControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $objectManager = $container->get('doctrine.entitymanager.orm_default');
        $database = $container->get('database');
        $informationSchema = $container->get('information-schema');
        $viewHelperManager = $container->get('ViewHelperManager');
        $config = $container->get('config');
        $console = $container->get('console');

        return new $requestedName(
            $objectManager,
            $database,
            $informationSchema,
            $viewHelperManager,
            $config,
            $console
        );
    }
}
