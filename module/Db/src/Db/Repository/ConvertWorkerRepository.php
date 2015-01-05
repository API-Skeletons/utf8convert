<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;


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

        return $this->_em->createQuery('SELECT COUNT(a.id) FROM Db\Entity\ConvertWorker a')
            ->getSingleScalarResult();
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
die($qb->getDql());
        $start = 0;
        $dataCount = 0;
        $paginator = new Paginator($qb->getQuery()->setFirstResult(0)->setMaxResults(500));
        while(true) {
            foreach ($paginator as $convertWorker) {
                $dataCount ++;
                $convertWorker->getDataPoint()->setNewValue($convertWorker->getValue());
                $_em->remove($convertWorker);

                echo "Found a valid data point";die();
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

        echo "\moved valid points to DataPoint\n";
    }
}