<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zend\Db\Adapter\Adapter;

class ConvertWorkerRepository extends EntityRepository
{
    public function truncate()
    {
        $connection = $this->_em->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('ConvertWorker'));
    }

    public function mutateValueField(Adapter $database, Entity\ColumnDef $column)
    {
        $convertColumnSql = 'ALTER TABLE ConvertWorker MODIFY `value` ';
        switch($column->getDataType()) {
            case 'varchar':
            case 'char':
            case 'enum':
                $convertColumnSql .= $column->getDataType() . '(' . $column->getLength() . ')';
                break;
            default:
                $convertColumnSql .= $column->getDataType();
                break;
        }
        if (!$column->getIsNullable()) {
            $convertColumnSql .= ' NOT NULL ';
        }
        if (!is_null($column->getDefaultValue())) {
            $convertColumnSql .= ' DEFAULT ' . $database->getPlatform()->quoteValue($column->getDefaultValue());
        }
        if ($column->getExtra()) {
            $convertColumnSql .= $column->getExtra();
        }

        $this->_em->getConnection()->exec($convertColumnSql);
    }

    public function fetchInvalidDataPoint(Entity\Conversion $conversion, Entity\ColumnDef $column)
    {
        $this->_em->getConnection()->exec("
            INSERT INTO ConvertWorker (data_point_id, value)
            SELECT id, oldValue FROM DataPoint
            WHERE conversion_id = " . $conversion->getId() . "
            AND column_def_id = " . $column->getId()
        );

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
            UPDATE DataPoint
            INNER JOIN ConvertWorker ON
                ConvertWorker.data_point_id = DataPoint.id
            SET DataPoint.newValue = ConvertWorker.value
        ");

        return $this->_em->createQuery('SELECT COUNT(a.id) FROM Db\Entity\ConvertWorker a')
            ->getSingleScalarResult();
    }
}