<?php

namespace Database\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use DoctrineModule\Authentication\Adapter\ObjectRepository;

final class TruncateControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $objectManager = $container->get('doctrine.entitymanager.orm_default');
        $console = $container->get('console');

        return new $requestedName(
            $objectManager,
            $console
        );
    }
}
