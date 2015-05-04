<?php
namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Datetime;
use Zend\Db\Adapter\Exception\RuntimeException;

class ConversionRepository extends EntityRepository
{
    public function setDataPointCount($conversion)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(dp.id)')
            ->from('Db\Entity\DataPoint', 'dp')
            ->andwhere('dp.conversion = :conversion')
            ->setParameter('conversion', $conversion)
            ;

        $conversion->setDataPointCount($qb->getQuery()->getSingleScalarResult());
    }

    public function import($conversion, $database)
    {
        $qb = $this->_em->createQueryBuilder();
        $errors = array();

        $qi = function($name) use ($database) { return $database->platform->quoteIdentifier($name); };
        $qv = function($value) use ($database) { return $database->platform->quoteValue($value); };

        $qb->select("dp")
            ->from('Db\Entity\DataPoint', 'dp')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('dp.approved = :approved')
            ->andwhere('dp.importedAt is null')
            ->setParameter('approved', true)
            ->setParameter('conversion', $conversion)
            ;

        $start = 0;
        $dataCount = 0;
        $paginator = new Paginator($qb->getQuery()->setFirstResult(0)->setMaxResults(500));
        while(true) {
            foreach ($paginator as $dataPoint) {
                if ($dataPoint->getImportedAt()) {
                    continue;
                }

                $rowDataPoints = $this->_em->getRepository('Db\Entity\DataPoint')->findBy(array(
                    'primaryKey' => $dataPoint->getPrimaryKey(),
                    'approved' => true,
                ));

                $sql = 'UPDATE '
                    . $qi($dataPoint->getColumnDef()->getTableDef()->getName())
                    . ' SET '
                    ;

                $columnSql = array();
                foreach ($rowDataPoints as $dp) {
                    $columnSql[] = $qi($dp->getColumnDef()->getName())
                        . ' = '
                        . $qv($dp->getNewValue());
                        ;
                }

                $sql .= implode(', ', $columnSql);

                $primaryKeySql = array();
                foreach ($dataPoint->getDataPointPrimaryKey() as $pk) {
                    $primaryKeySql[] = $qi($pk->getPrimaryKeyDef()->getName())
                        . ' = '
                        . $qv($pk->getValue())
                        ;
                }

                $sql .= ' WHERE '
                    . implode(' AND ', $primaryKeySql)
                    ;

                try {
                    $database->query($sql)->execute();
                    foreach ($rowDataPoints as $dp) {
                        $dp->setImportedAt(new Datetime());
                    }
                    $this->_em->flush();
                } catch (RuntimeException $e) {
                    $errors[] = array(
                        'message' => $e->getMessage(),
                        'sql' => $sql,
                    );
                }

                $dataCount ++;
            }

            $this->_em->clear();

            if (!$dataCount) {
                break;
            }
            $dataCount = 0;

            $start += 500;
            $paginator->getQuery()->setFirstResult($start);
        }

        return $errors;
    }
}
