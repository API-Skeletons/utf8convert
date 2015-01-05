<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DataPointIterationRepository extends EntityRepository
{
    public function audit($iteration)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("cw")
            ->from('Db\Entity\ConvertWorker', 'cw')
            ;

        $start = 0;
        $dataCount = 0;
        $paginator = new Paginator($qb->getQuery()->setFirstResult(0)->setMaxResults(500));
        while(true) {
            foreach ($paginator as $convertWorker) {
                $dataCount ++;
                $dataPointIteration = new Entity\DataPointIteration();
                $dataPointIteration->setIteration($iteration);
                $dataPointIteration->setDataPoint($convertWorker->getDataPoint());
                $dataPointIteration->setValue($convertWorker->getValue());

                $this->_em->persist($dataPointIteration);
            }

            if (!$dataCount) {
                break;
            }
            $dataCount = 0;

            $this->_em->flush();
            $this->_em->clear();
            $start += 100;
            $paginator->getQuery()->setFirstResult($start);
        }
    }
}