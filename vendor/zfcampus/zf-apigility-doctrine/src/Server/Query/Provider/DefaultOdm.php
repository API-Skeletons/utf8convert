<?php

namespace ZF\Apigility\Doctrine\Server\Query\Provider;

use ZF\Apigility\Doctrine\Server\Query\Provider\QueryProviderInterface;
use ZF\Apigility\Doctrine\Server\Paginator\Adapter\DoctrineOdmAdapter;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\ApiProblem\ApiProblem;

class DefaultOdm implements QueryProviderInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function createQuery($entityClass, $parameters)
    {
        /**
         * @var \Doctrine\Odm\MongoDB\Query\Builder $queryBuilder
         */
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->find($entityClass);

        return $queryBuilder;
    }

    /**
     * @param   $queryBuilder
     *
     * @return DoctrineOdmAdapter
     */
    public function getPaginatedQuery($queryBuilder)
    {
        $adapter = new DoctrineOdmAdapter($queryBuilder);

        return $adapter;
    }

    /**
     * @param   $entityClass
     *
     * @return int
     */
    public function getCollectionTotal($entityClass)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->find($entityClass);
        $count = $queryBuilder->getQuery()->execute()->count();

        return $count;
    }
}
