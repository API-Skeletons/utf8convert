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
        $this->_em->getConnection()->exec("
            UPDATE DataPoint SET newValue = (
            SELECT value FROM ConvertWorker
            WHERE DataPoint.id = ConvertWorker.data_point_id
            )
        ");

        return $this->_em->createQuery('SELECT COUNT(a.id) FROM Db\Entity\ConvertWorker a')
            ->getSingleScalarResult();
    }
}