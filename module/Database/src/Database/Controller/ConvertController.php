<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use DateTime;
use Db\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class ConvertController extends AbstractActionController
{
    /**
     * Convert all data in all string columns to utf8 by correcting encoding
     */
    public function convertAction()
    {
        $consoleWhitelist = $this->getRequest()->getParam('whitelist');
        $consoleBlacklist = $this->getRequest()->getParam('blacklist');

        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $config = $this->getServiceLocator()->get('Config');
        $tableDefConversion = new ArrayCollection();


        $informationSchema->query("SET session wait_timeout=86400")->execute();

        $blacklistTables = '';
        if ($consoleBlacklist) {
            $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", explode(',', $consoleBlacklist))
                . "')";
        } else {
            if (isset($config['utf8-convert']['convert']['blacklist-tables']) and $config['utf8-convert']['convert']['blacklist-tables']) {
                $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", $config['utf8-convert']['convert']['blacklist-tables'])
                    . "')";
            }
        }

        $whitelistTables= '';
        if ($consoleWhitelist) {
            $whitelistTables = "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", explode(',', $consoleWhitelist))
                . "')";
        } else {
            $whitelistTables= '';
            if (isset($config['utf8-convert']['convert']['whitelist-tables']) and $config['utf8-convert']['convert']['whitelist-tables']) {
                $whitelistTables= "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", $config['utf8-convert']['convert']['whitelist-tables'])
                    . "')";
            }
        }

/*
        $whitelistTables = $config['utf8-convert']['convert']['whitelist-tables'] + $consoleWhitelist;
        if ($whitelistTables) {
            $whitelistTables= "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", $whitelistTables)
                . "')";
        }

        $blacklistTables = $config['utf8-convert']['convert']['blacklist-tables'] + $consoleBlacklist;
        if ($blacklistTables) {
            $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", $blacklistTables)
                . "')";
        }
*/
        $convertColumns = $informationSchema->query("
            SELECT COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME, COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH
            FROM COLUMNS, TABLES
            WHERE COLUMNS.TABLE_SCHEMA = ?
            AND COLUMNS.TABLE_NAME = TABLES.TABLE_NAME
            AND TABLES.TABLE_SCHEMA = COLUMNS.TABLE_SCHEMA
            AND TABLES.TABLE_TYPE = 'BASE TABLE'
            AND COLUMNS.DATA_TYPE IN ('varchar', 'char', 'enum', 'tinytext', 'text', 'mediumtext', 'longtext')
            ORDER BY COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME
        ", array($databaseConnection['database']));

        $conversion = new Entity\Conversion;
        $conversion->setCreatedAt(new DateTime());
        $objectManager->persist($conversion);
        $objectManager->flush();

        $conversionKey = $conversion->getId();

        foreach ($convertColumns as $row) {
            $convertPrimaryKeys = $informationSchema->query("
                SELECT COLUMN_NAME, COLUMN_KEY
                  FROM COLUMNS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND column_key = 'PRI'
              ORDER BY COLUMN_NAME
            ", array($databaseConnection['database'], $row['TABLE_NAME']));

            if (!sizeof($convertPrimaryKeys)) {
                echo "\nThe table " . $row['TABLE_NAME'] . " has no primary key.\n";
                return;
            }

            $table = $objectManager->getRepository('Db\Entity\TableDef')->findOneBy(array(
                'name' => $row['TABLE_NAME'],
            ));

            if (!$table) {
                $table = new Entity\TableDef;
                $table->setName($row['TABLE_NAME']);

                $objectManager->persist($table);
            }

            if (!$tableDefConversion->contains($table) and !$table->getConversion()->contains($conversion)) {
                $conversion->addTableDef($table);
                $table->addConversion($conversion);
                $tableDefConversion->add($table);
            }


            $primaryKeys = array();
            foreach ($convertPrimaryKeys as $primaryKeyColumn) {
                $primaryKey = $objectManager->getRepository('Db\Entity\PrimaryKeyDef')->findOneBy(array(
                    'tableDef' => $table,
                    'name' => $primaryKeyColumn['COLUMN_NAME'],
                ));

                if (!$primaryKeyColumn) {
                    $primaryKey = new Entity\PrimaryKeyDef;
                    $primaryKey->setTableDef($table);
                    $primaryKey->setName($primaryKeyColumn['COLUMN_NAME']);

                    $objectManager->persist($primaryKey);
                }
            }

            $objectManager->flush();

            $this->convertColumn($conversion, $table, $row);

            // Re-fetch working entities
            $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionKey);
        }

        echo "\nConversion complete\n";
    }

    /**
     * Convert a single row to utf8 and interate until
     * there are no more conversions to be made
     */
    private function convertColumn(Entity\Conversion $conversion, Entity\TableDef $table, $row)
    {
        $database = $this->getServiceLocator()->get('database');
        $config = $this->getServiceLocator()->get('Config');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $refactorDataTypes = $config['utf8-convert']['refactor']['data-types'];
        $databaseConnection = $config['db']['adapters']['database'];
        $informationSchema = $this->getServiceLocator()->get('information-schema');

        $columns = array();
        $conversionKey = $conversion->getId();
        $tableKey = $table->getId();

        if (!$table->getPrimaryKeyDef()) return;

        foreach ($table->getPrimaryKeyDef() as $primaryKey) {
            $columns[] = $primaryKey->getName();
        }
        $columns[] = $row['COLUMN_NAME'];

        $select = new Select($row['TABLE_NAME']);
        $select->columns($columns);
        $where = new Where();
        $where->addPredicate(new Predicate\Expression(
            "length(`"
            . $row['COLUMN_NAME'] . "`) != char_length(`"
            . $row['COLUMN_NAME'] . "`)")
        );
        $select->where($where);

        $resultSetPrototype = new ResultSet();
        $paginatorAdapter = new DbSelect(
            // our configured select object
            $select,
            // the adapter to run it against
            $database,
            // the result set to hydrate
            $resultSetPrototype
        );
        $paginator = new Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);

        echo $paginator->getTotalItemCount() . " records " . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'] . "\n";

        $page = 1;
        $processing = true;
        $rowCount = 0;
        while ($paginator->count()) {
            foreach ($paginator as $utf8record) {
                $rowCount ++;

#print_r($utf8record);

#echo "running record $rowCount of " . $paginator->getTotalItemCount() . "\n";
                $column = $objectManager->getRepository('Db\Entity\ColumnDef')->findOneBy(array(
                    'tableDef' => $table,
                    'name' => $row['COLUMN_NAME'],
                ));

                if (!$column) {
                    $column = new Entity\ColumnDef();
                    $column->setTableDef($table);
                    $column->setName($row['COLUMN_NAME']);

                    $columnDefinition = $informationSchema->query("
                        SELECT
                            COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH,
                            COLUMNS.IS_NULLABLE, COLUMNS.COLUMN_DEFAULT
                        FROM COLUMNS
                        WHERE COLUMNS.TABLE_SCHEMA = ?
                            AND COLUMNS.TABLE_NAME = ?
                            AND COLUMNS.COLUMN_NAME = ?
                   ", array($databaseConnection['database'], $row['TABLE_NAME'], $row['COLUMN_NAME']));

                    if (sizeof($columnDefinition) != 1) {
                        echo "\nCannot fetch definition of " . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'] . "\n";
                    }

                    foreach ($columnDefinition as $columnDef) {
                        $column->setDataType($columnDef['DATA_TYPE']);
                        $column->setLength($columnDef['CHARACTER_MAXIMUM_LENGTH']);
                        $column->setDefaultValue($columnDef['COLUMN_DEFAULT']);
                        $column->setIsNullable($columnDef['IS_NULLABLE']);
                        $column->setExtra($columnDef['EXTRA']);
                    }

                    $objectManager->persist($column);
                }

                $values = array();
                foreach ($table->getPrimaryKeyDef() as $primaryKey) {
                    $values[] = $utf8record[$primaryKey->getColumn()];
                }
                $primaryKeyString = implode(',', $values);

                $dataPoint = new Entity\DataPoint();
                $dataPoint->setColumnDef($column);
                $dataPoint->setConversion($conversion);
                $dataPoint->setOldValue($utf8record[$column->getName()]);
                $dataPoint->setIteration(1);
                $dataPoint->setFlagForReview(false);
                $dataPoint->setPrimaryKey($primaryKeyString);

                $objectManager->persist($dataPoint);
            }

            $objectManager->flush();
            $objectManager->clear();

            // Re-fetch working entities
            $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionKey);
            $table = $objectManager->getRepository('Db\Entity\TableDef')->find($tableKey);

            if ($rowCount > $paginator->getTotalItemCount()) {
                break;
            }

            $page ++;
            $paginator->setCurrentPageNumber($page);
        }
    }
}

