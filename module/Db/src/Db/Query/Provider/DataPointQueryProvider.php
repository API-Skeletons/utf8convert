<?php

namespace Db\Query\Provider;

use ZF\Apigility\Doctrine\Server\Collection\Query\FetchAllOrmQuery;

class DataPointQueryProvider extends FetchAllOrmQuery
{
    /**
     * Create a filtered query with required parameters
     */
    public function createQuery($entityClass, $parameters)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();

        $queryBuilder->select('row')
            ->from($entityClass, 'row')
            ->andwhere('row.conversion = :conversion')
            ->andwhere('row.columnDef = :column')
            ->setParameter('conversion', $parameters['conversion'])
            ->setParameter('column', $parameters['column'])
            ;

        return $queryBuilder;
    }
}
