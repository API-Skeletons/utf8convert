<?php

namespace DataVisualization\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Db\Entity;

/**
 * Helper for fetching ContentManagement Pages
 */
class CountDataPointApproved extends AbstractHelper implements ServiceLocatorAwareInterface
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
    public function __invoke(Entity\Conversion $conversion, Entity\ColumnDef $column)
    {
        $objectManager = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $qb = $objectManager->createQueryBuilder();
        $qb->select('count(dp.id)')
            ->from('Db\Entity\DataPoint', 'dp')
            ->from('Db\Entity\ColumnDef', 'cd')
            ->andwhere('dp.columnDef = cd')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('cd = :columnDef')
            ->andwhere('dp.approved = :approved')
            ->setParameter('conversion', $conversion)
            ->setParameter('columnDef', $column)
            ->setParameter('approved', true)
            ;

#            die($qb->getQuery()->getDql());

        return $qb->getQuery()->getSingleScalarResult();
    }
}
