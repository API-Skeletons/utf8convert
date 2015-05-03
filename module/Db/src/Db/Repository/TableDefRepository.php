<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zend\Db\Adapter\Adapter;

class TableDefRepository extends EntityRepository
{
    public function url($dataPoint) {
        $tableDef = $dataPoint->getColumnDef()->getTableDef();

        $urlTemplate = $tableDef->getUrl();

        foreach ($dataPoint->getDataPointPrimaryKey() as $key) {
            $urlTemplate = str_replace(
                '<' . $key->getPrimaryKeyDef()->getName() . '>',
                $key->getValue(),
                $urlTemplate);
        }

        return $urlTemplate;
    }
}