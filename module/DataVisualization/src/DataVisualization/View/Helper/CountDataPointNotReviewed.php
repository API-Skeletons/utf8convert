<?php

namespace DataVisualization\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Db\Entity;

/**
 * Helper for fetching ContentManagement Pages
 */
class CountDataPointNotReviewed extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     */
    public function __invoke(Entity\Conversion $conversion, Entity\ColumnDef $column, $imported = false)
    {
        $objectManager = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $qb = $objectManager->createQueryBuilder();
        $qb->select('count(dp.id)')
            ->from('Db\Entity\DataPoint', 'dp')
            ->from('Db\Entity\ColumnDef', 'cd')
            ->andwhere('dp.columnDef = cd')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('cd = :columnDef')
            ->andwhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('dp.approved', ':approved'),
                    $qb->expr()->isNull('dp.approved')
                )
            )
            ->andwhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('dp.flagged', ':flagged'),
                    $qb->expr()->isNull('dp.flagged')
                )
            )
            ->andwhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('dp.denied', ':denied'),
                    $qb->expr()->isNull('dp.denied')
                )
            )
            ->setParameter('conversion', $conversion)
            ->setParameter('columnDef', $column)
            ->setParameter('approved', false)
            ->setParameter('flagged', false)
            ->setParameter('denied', false)
            ;

        if ($imported) {
            $qb->andwhere('dp.importedAt IS NOT NULL');
        } else {
            $qb->andwhere('dp.importedAt IS NULL');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
