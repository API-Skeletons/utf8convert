<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ConversionRepository extends EntityRepository
{
	public function setDataPointCount($conversion) 
	{
	die('set data point conversion');
		$qb = $this->_em->createQueryBuilder();
		$qb->select('count(dp.id)')
			->from('Db\Entity\DataPoint' 'db')
			->andwhere('db.conversion = :conversion')
			->setParameter('conversion', $conversion)
			;

	    $conversion->setDataPointCount($qb->getQuery()->getSingleScalarResult();
	}
}
