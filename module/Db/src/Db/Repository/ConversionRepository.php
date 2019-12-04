<?php

namespace Db\Repository;

use Doctrine\ORM\EntityRepository;
use Db\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Datetime;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use Zend\Console\Adapter\Posix;
use Zend\ProgressBar\Adapter\Console as ProgressBarConsoleAdaper;
use Zend\ProgressBar\ProgressBar;

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

    public function export(Entity\Conversion $conversion, $database, AdapterInterface $console)
    {
        set_time_limit(0);

        $qb = $this->_em->createQueryBuilder();
        $errors = array();

        $qi = function($name) use ($database) {
            return $database->platform->quoteIdentifier($name);
        };
        $qv = function($value) use ($database) {
            return $database->platform->quoteValue($value);
        };

        $qb->select("dp")
            ->from('Db\Entity\DataPoint', 'dp')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('dp.approved = :approved')
            ->andWhere('dp.importedAt is null')
            ->andwhere($qb->expr()->isnull('dp.importedAt'))
            ->setParameter('approved', true)
            ->setParameter('conversion', $conversion)
            ;

        $start = 0;
        $dataCount = 0;
        $rowCount = 0;

        $paginator = new Paginator($qb->getQuery()->setFirstResult(0)->setMaxResults(500));
        $limit = $paginator->count() < 500 ? $paginator->count() : 500;
        if ($limit) {
            $console->writeLine("Start export converted DataPoints to target database", Color::YELLOW);
        } else {
            $console->writeLine('No DataPoints to export', Color::YELLOW);

            return [];
        }

        while($limit) {
            $rowCount = 0;

            $paginator = new Paginator($qb->getQuery()->setFirstResult(0)->setMaxResults($limit));
            $limit = $paginator->count() < 500 ? $paginator->count() : 500;
            if (! $limit) {
                break;
            }

            $console->writeLine($paginator->count() . " DataPoints to export.  Running $limit.", Color::GREEN);
            if ($limit) {
                $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 1, $limit);
            }
            foreach ($paginator as $dataPoint) {

                $rowCount ++;

                $queryBuilder = $this->_em->createQueryBuilder();
                $queryBuilder->select('row')
                    ->from('Db\Entity\DataPoint', 'row')
                    ->innerJoin('row.columnDef', 'columnDef')
                    ->andWhere('row.approved = :approved')
                    ->andWhere('row.primaryKey = :primaryKey')
                    ->andWhere('columnDef.tableDef = :tableDef')
                    ->setParameter('approved', true)
                    ->setParameter('primaryKey', $dataPoint->getPrimaryKey())
                    ->setParameter('tableDef', $dataPoint->getColumnDef()->getTableDef())
                    ;

                $rowDataPoints = $queryBuilder->getQuery()->getResult();

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

                foreach ($dataPoint->getDataPointPrimaryKeyDef() as $pk) {
                    $primaryKeySql[] = $qi($pk->getPrimaryKeyDef()->getName())
                        . ' = '
                        . $qv($pk->getValue())
                        ;
                }

                $sql .= ' WHERE '
                    . implode(' AND ', $primaryKeySql)
                    ;

                try {
#                    echo $sql . "; \n";
                    $database->query($sql)->execute();
                    foreach ($rowDataPoints as $dp) {
                        $dp->setImportedAt(new Datetime());
                    }
                    $this->_em->flush();
                    $progressBar->update($rowCount);

                } catch (RuntimeException $e) {
                    throw $e;
                    foreach ($rowDataPoints as $dp) {
                        $errors[] = array(
                            'dataPoint' => $dp->getId(),
                            'message' => $e->getMessage(),
                            'sql' => $sql,
                        );
                    }
                }

                $sql = '';
                $dataCount ++;
            }

            $this->_em->clear();

            if (! $dataCount) {
                break;
            }
            $dataCount = 0;

            $start += $limit;
            $paginator->getQuery()->setFirstResult($start);
        }

        if ($errors) {
            print_r($errors);
            die('found errors');
        }

        return $errors;
    }
}
