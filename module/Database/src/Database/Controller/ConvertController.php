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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ConvertController extends AbstractActionController
{
    public function copyConversionAction()
    {
        $fromConversionName = $this->getRequest()->getParam('from');
        $toConversionName = $this->getRequest()->getParam('to');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $fromConversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $fromConversionName
        ));

        if (!$fromConversion) {
            die("\nThe from conversion name was not found\n");
        }

        try {
            $conversion = new Entity\Conversion;
            $conversion->setCreatedAt($fromConversion->getCreatedAt());
            $conversion->setName($toConversionName);
            if ($fromConversion->getStartAt()) {
                $conversion->setStartAt($fromConversion->getStartAt());
                $conversion->setEndAt($fromConversion->getEndAt());
            }
            $objectManager->persist($conversion);

            foreach ($fromConversion->getTableDef() as $table) {
                $table->addConversion($conversion);
                $conversion->addTableDef($table);
            }

            $objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            die("\nThe conversion name " . $toConversionName . " has already been used\n");
        }

        $objectManager->getConnection()->exec("
            INSERT INTO DataPoint (conversion_id, column_def_id, primaryKey, oldValue, newValue, flagForReview, user_id)
                SELECT " . $conversion->getId() . ", column_def_id, primaryKey, oldValue, newValue, flagForReview, user_id
                FROM DataPoint
                WHERE conversion_id = " . $fromConversion->getId() . "
        ");

        echo "\nConversion copy complete\n";
    }

    public function runConversionAction()
    {
        $conversionName = $this->getRequest()->getParam('name');

        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $database = $this->getServiceLocator()->get('database');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $informationSchema->query("SET session wait_timeout=86400")->execute();

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $conversionName,
        ));

        if (!$conversion) {
            echo "Conversion $conversionName cannot be found.  Use --conversion=name\n";
            return;
        }

        if ($conversion->getStartAt()) {
            echo "This conversion has already been ran\n";
            return;
        }

        $conversion->setStartAt(new DateTime());
        $objectManager->flush();

        foreach ($conversion->getTableDef() as $table) {
            foreach ($table->getColumnDef() as $column) {
                $iteration = 1;
                $objectManager->getRepository('Db\Entity\ConvertWorker')->truncate();
                $objectManager->getRepository('Db\Entity\ConvertWorker')->mutateValueField($database, $column);
                $objectManager->getRepository('Db\Entity\ConvertWorker')->fetchInvalidDataPoint($conversion, $column);
                $objectManager->getRepository('Db\Entity\ConvertWorker')->utf8Convert();
                $objectManager->getRepository('Db\Entity\ConvertWorker')->moveValidDataPoint();
#                $objectManager->getRepository('Db\Entity\DataPointIteration')->audit($iteration);
                $objectManager->getRepository('Db\Entity\ConvertWorker')->truncate();

                $revertColumn = new Entity\ColumnDef();
                $revertColumn->setDataType('longtext');
                $objectManager->getRepository('Db\Entity\ConvertWorker')->mutateValueField($database, $revertColumn);
            }
        }

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $conversionName,
        ));

        $conversion->setEndAt(new DateTime());
        $objectManager->flush();
    }

    /**
     * Convert all data in all string columns to utf8 by correcting encoding
     */
    public function createConversionAction()
    {
        $conversionName = $this->getRequest()->getParam('name');
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
            $whitelistTables
            $blacklistTables
            ORDER BY COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME
        ", array($databaseConnection['database']));

        try {
            $conversion = new Entity\Conversion;
            $conversion->setCreatedAt(new DateTime());
            $conversion->setName($conversionName);
            $objectManager->persist($conversion);

            $objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            die("\nThe conversion name " . $conversionName . " has already been used\n");
        }

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
                continue;
            }

            $table = $objectManager->getRepository('Db\Entity\TableDef')->findOneBy(array(
                'name' => $row['TABLE_NAME'],
            ));

            if (!$table) {
                $table = new Entity\TableDef;
                $table->setName($row['TABLE_NAME']);

                $objectManager->persist($table);
                $objectManager->flush();
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

                if (!$primaryKey) {
                    $primaryKey = new Entity\PrimaryKeyDef;
                    $primaryKey->setTableDef($table);
                    $primaryKey->setName($primaryKeyColumn['COLUMN_NAME']);

                    $objectManager->persist($primaryKey);
                    $objectManager->flush();
                }
            }

            $objectManager->flush();

            $objectManager->refresh($table);
            $this->convertColumn($conversion, $table, $row);

            // Re-fetch working entities
            $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionKey);
        }

        $viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
        $countDataPoint = $viewHelperManager->get('countDataPoint');

        foreach ($conversion->getTableDef() as $table) {
            $count = 0;
            foreach ($table->getColumnDef() as $column) {
                $count += $countDataPoint($conversion, $column);
            }
            if (!$count) {
                $table->removeConversion($conversion);
                $conversion->removeTableDef($table);
            }
        }

        $objectManager->flush();

        echo "\nConversion created\n";
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

        if (!sizeof($table->getPrimaryKeyDef())) return;

        $columns = array();
        foreach ($table->getPrimaryKeyDef() as $primaryKey) {
            $columns[$primaryKey->getName()] = $primaryKey->getName();
        }
        $columns[$row['COLUMN_NAME']] = $row['COLUMN_NAME'];

        $select = new Select($row['TABLE_NAME']);
        $select->columns($columns);
        $where = new Where();
        $where->addPredicate(new Predicate\Expression(
            "length(`"
            . $row['COLUMN_NAME']
            . "`) != char_length(`"
            . $row['COLUMN_NAME']
            . "`)"
        ));
        $select->where($where);

        $resultSetPrototype = new ResultSet();
        $paginatorAdapter = new DbSelect(
            $select,
            $database,
            $resultSetPrototype
        );

        $page = 1;

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        $rowCount = 0;
        while (true) {
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
                        $column->setIsNullable(($columnDef['IS_NULLABLE'] == 'YES'));
                        $column->setExtra($columnDef['EXTRA']);
                    }

                    $objectManager->persist($column);
                    $objectManager->flush();
                }

                $values = array();
                foreach ($table->getPrimaryKeyDef() as $primaryKey) {
                    $values[] = $utf8record[$primaryKey->getName()];
                }
                $primaryKeyString = implode(',', $values);

                $dataPoint = new Entity\DataPoint();
                $dataPoint->setColumnDef($column);
                $dataPoint->setConversion($conversion);
                $dataPoint->setOldValue($utf8record[$column->getName()]);
                $dataPoint->setNewValue($utf8record[$column->getName()]);
                $dataPoint->setFlagForReview(false);
                $dataPoint->setPrimaryKey($primaryKeyString);

                foreach ($table->getPrimaryKeyDef() as $primaryKey) {
                    $dataPointPrimaryKey = new Entity\DataPointPrimaryKeyDef();
                    $dataPointPrimaryKey->setPrimaryKeyDef($primaryKey);
                    $dataPointPrimaryKey->setDataPoint($dataPoint);
                    $dataPointPrimaryKey->setValue($utf8record[$primaryKey->getName()]);

                    $objectManager->persist($dataPointPrimaryKey);
                }

                $objectManager->persist($dataPoint);
            }

            $objectManager->flush();
            $objectManager->clear();
/**
 * this needs to be improved
 */
            // Re-fetch working entities
            $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionKey);
            $table = $objectManager->getRepository('Db\Entity\TableDef')->find($tableKey);

            if ($rowCount == $paginator->getTotalItemCount()) {
                break;
            }

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
                $select,
                $database,
                $resultSetPrototype
            );

            $page ++;
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        }
    }
}

