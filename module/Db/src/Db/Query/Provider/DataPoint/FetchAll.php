<?php

namespace Db\Query\Provider\DataPoint;

use ZF\Apigility\Doctrine\Server\Query\Provider\DefaultOrm;
use ZF\Rest\ResourceEvent;

class FetchAll extends DefaultOrm
{
    /**
     * Create a filtered query with required parameters
     */
    public function createQuery(ResourceEvent $event, $entityClass, $parameters)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();

        $queryBuilder->select('row')
            ->from($entityClass, 'row')
            ->andwhere('row.conversion = :conversion')
            ->andwhere('row.columnDef = :column')
            ->andwhere('row.importedAt is null')
            ->setParameter('conversion', $parameters['conversion'])
            ->setParameter('column', $parameters['column'])
            ->orderby('row.oldValue')
            ;

        return $queryBuilder;
    }
}
