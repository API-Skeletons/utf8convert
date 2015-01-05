<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;

class ConvertWorkerRepository extends EntityRepository
{
    public function truncate()
    {
        $connection = $this->_em->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('ConvertWorker'));
    }

    public function fetchInvalidDataPoint(Entity\Conversion $conversion)
    {
        $this->_em->getConnection()->exec("
            INSERT INTO ConvertWorker (data_point_id, value)
            SELECT id, newValue FROM DataPoint
            WHERE length(newValue) != char_length(newValue)
            AND conversion_id = " . $conversion->getId());

        return sizeof($this->findAll());
    }

    public function utf8Convert()
    {
        $this->_em->getConnection()->exec("ALTER TABLE ConvertWorker modify value longtext character set latin1");
        $this->_em->getConnection()->exec("ALTER TABLE ConvertWorker modify value blob");
        $this->_em->getConnection()->exec("ALTER TABLE ConvertWorker modify value longtext character set utf8");
    }

    public function moveValidDataPoint()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("cw")
            ->from('Db\Entity\ConvertWorker', 'cw')
            ->where('length(cw.value) = char_length(cw.value)')
            ;

        $result = $qb->getQuery()->getResult();

$count = 0;
        foreach ($result as $worker) {
            $worker->getDataPoint()->setNewValue($worker->getValue());

            $this->_em->remove($worker);
            $count ++;
        }


        echo "\nValidated $count points\n";

    }
}