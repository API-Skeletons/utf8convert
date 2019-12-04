<?php

namespace Database\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use DoctrineModule\Authentication\Adapter\ObjectRepository;

final class CountDataPointFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $objectManager = $container->get('doctrine.entitymanager.orm_default');

        return new CountDataPoint(
            $objectManager
        );
    }
}
