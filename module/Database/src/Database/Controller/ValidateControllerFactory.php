<?php

namespace Database\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use DoctrineModule\Authentication\Adapter\ObjectRepository;

final class ValidateControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $database = $container->get('database');
        $informationSchema = $container->get('information-schema');
        $config = $container->get('config');
        $console = $container->get('console');

        return new $requestedName(
            $database,
            $informationSchema,
            $config,
            $console
        );
    }
}
