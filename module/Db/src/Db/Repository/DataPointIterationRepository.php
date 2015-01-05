<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;

class DataPointIterationRepository extends EntityRepository
{
    public function audit($iteration)
    {
        $convertWorker = $this->_em->getRepository('Db\Entity\ConvertWorker')->findAll();

        foreach ($convertWorker as $worker) {
            $dataPointIteration = new Entity\DataPointIteration();
            $dataPointIteration->setIteration($iteration);
            $dataPointIteration->setDataPoint($worker->getDataPoint());
            $dataPointIteration->setValue($worker->getValue());

            $this->_em->persist($worker);
        }
    }
}