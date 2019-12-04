<?php

namespace Database\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Db\Entity;

/**
 * Helper for fetching ContentManagement Pages
 */
final class CountDataPoint extends AbstractHelper implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    public function __construct($objectManager)
    {
        $this->setObjectManager($objectManager);
    }

    public function __invoke(Entity\Conversion $conversion, Entity\ColumnDef $column, $imported = false)
    {
        $qb = $this->getObjectManager()->createQueryBuilder();
        $qb->select('count(dp.id)')
            ->from('Db\Entity\DataPoint', 'dp')
            ->from('Db\Entity\ColumnDef', 'cd')
            ->andwhere('dp.columnDef = cd')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('cd = :columnDef')
            ->setParameter('conversion', $conversion)
            ->setParameter('columnDef', $column)
            ;

        if ($imported) {
            $qb->andwhere('dp.importedAt IS NOT NULL');
        } else {
            $qb->andwhere('dp.importedAt IS NULL');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
