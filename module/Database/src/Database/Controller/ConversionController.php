<?php

namespace Database\Controller;

use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\DBAL\Exception\DriverException;

use Db\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ApiSkeletons\Utf8;

use Zend\Console\Request as ConsoleRequest;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use Zend\Console\Adapter\Posix;
use Zend\ProgressBar\Adapter\Console as ProgressBarConsoleAdaper;
use Zend\ProgressBar\ProgressBar;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use DateTime;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

class ConversionController extends AbstractConsoleController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    private $database;
    private $informationSchema;
    private $config;
    private $viewHelperManager;

    public function __construct(
        $objectManager,
        DbAdapter $database,
        DbAdapter $informationSchema,
        $viewHelperManager,
        array $config,
        ConsoleAdapterInterface $console
    ) {
        $this->setObjectManager($objectManager);
        $this->database = $database;
        $this->informationSchema = $informationSchema;
        $this->viewHelperManager = $viewHelperManager;
        $this->config = $config;
        $this->setConsole($console);
    }

    /**
     * Convert all data in all string columns to utf8 by correcting encoding
     */
    public function createAction()
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $conversionName = $this->getRequest()->getParam('name');
        $consoleWhitelist = $this->getRequest()->getParam('whitelist');
        $consoleBlacklist = $this->getRequest()->getParam('blacklist');

        $databaseConnection = $this->config['db']['adapters']['database'];
        $tableDefConversion = new ArrayCollection();

        $this->informationSchema->query("SET session wait_timeout=86400")->execute();

        $blacklistTables = '';
        if ($consoleBlacklist) {
            $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", explode(',', $consoleBlacklist))
                . "')";
        } else {
            if (isset($this->config['utf8-convert']['convert']['blacklist-tables']) and $this->config['utf8-convert']['convert']['blacklist-tables']) {
                $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", $this->config['utf8-convert']['convert']['blacklist-tables'])
                    . "')";
            }
        }

        $whitelistTables= '';
        if ($consoleWhitelist) {
            $whitelistTables = "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", explode(',', $consoleWhitelist))
                . "')";
        } else {
            $whitelistTables= '';
            if (isset($this->config['utf8-convert']['convert']['whitelist-tables']) and $this->config['utf8-convert']['convert']['whitelist-tables']) {
                $whitelistTables= "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", $this->config['utf8-convert']['convert']['whitelist-tables'])
                    . "')";
            }
        }

/*
        $whitelistTables = $this->config['utf8-convert']['convert']['whitelist-tables'] + $consoleWhitelist;
        if ($whitelistTables) {
            $whitelistTables= "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", $whitelistTables)
                . "')";
        }

        $blacklistTables = $this->config['utf8-convert']['convert']['blacklist-tables'] + $consoleBlacklist;
        if ($blacklistTables) {
            $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", $blacklistTables)
                . "')";
        }
*/
        $convertColumns = $this->informationSchema->query("
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

        $this->console->writeLine("Creating Conversion", Color::CYAN);

        try {
            $conversion = new Entity\Conversion;
            $conversion->setCreatedAt(new DateTime());
            $conversion->setName($conversionName);
            $this->getObjectManager()->persist($conversion);

            $this->getObjectManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            die("\nThe conversion name " . $conversionName . " has already been used\n");
        }

        $conversionKey = $conversion->getId();
        foreach ($convertColumns as $row) {
            $convertPrimaryKeys = $this->informationSchema->query("
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

            $table = $this->getObjectManager()->getRepository('Db\Entity\TableDef')->findOneBy(array(
                'name' => $row['TABLE_NAME'],
            ));

            if (!$table) {
                $table = new Entity\TableDef;
                $table->setName($row['TABLE_NAME']);

                $this->getObjectManager()->persist($table);
                $this->getObjectManager()->flush();
            }

            if (!$tableDefConversion->contains($table) and !$table->getConversion()->contains($conversion)) {
                $conversion->addTableDef($table);
                $table->addConversion($conversion);
                $tableDefConversion->add($table);
            }


            $primaryKeys = array();
            foreach ($convertPrimaryKeys as $primaryKeyColumn) {
                $primaryKey = $this->getObjectManager()->getRepository('Db\Entity\PrimaryKeyDef')->findOneBy(array(
                    'tableDef' => $table,
                    'name' => $primaryKeyColumn['COLUMN_NAME'],
                ));

                if (!$primaryKey) {
                    $primaryKey = new Entity\PrimaryKeyDef;
                    $primaryKey->setTableDef($table);
                    $primaryKey->setName($primaryKeyColumn['COLUMN_NAME']);

                    $this->getObjectManager()->persist($primaryKey);
                    $this->getObjectManager()->flush();
                }
            }

            $this->getObjectManager()->flush();

            $this->getObjectManager()->refresh($table);
            $this->convertColumn($conversion, $table, $row);

            // Re-fetch working entities
            $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->find($conversionKey);
        }

        $countDataPoint = $this->viewHelperManager->get('countDataPoint');

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

        $this->getObjectManager()->flush();

        $this->console->writeLine("Conversion created", Color::GREEN);
    }

    /**
     * Run a conversion
     */
    public function convertAction()
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $conversionName = $this->getRequest()->getParam('name');
        $forceConversion = $this->getRequest()->getParam('force');

        $databaseConnection = $this->config['db']['adapters']['database'];

        $this->informationSchema->query("SET session wait_timeout=86400")->execute();

        $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $conversionName,
        ));

        if (!$conversion) {
            $this->console->writeLine("Conversion $conversionName cannot be found.  Use --conversion=name", Color::RED);
            return;
        }

        $conversion->setStartAt(new DateTime());
        $this->getObjectManager()->flush();

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('dataPoint')
            ->from('Db\Entity\DataPoint', 'dataPoint')
            ->andwhere($queryBuilder->expr()->eq('dataPoint.conversion', ':conversion'))
            ->setParameter('conversion', $conversion)
            ->addOrderBy('dataPoint.id', 'DESC')
            ;

        $page = 1;
        $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($this->config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        $this->console->writeLine("Converting " . $conversion->getName(), Color::CYAN);
        $this->console->writeLine($paginator->getTotalItemCount() . " DataPoints", Color::YELLOW);
        $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 1, $paginator->getTotalItemCount());

        $correctUtf8Encoding = new Utf8\CorrectUtf8Encoding();

        $rowCount = 0;

        while ($page <= $paginator->count()) {
#            echo "Set page: $page, rowCount: $rowCount, Total Pages: " . $paginator->count() . "\n";
            foreach ($paginator as $dataPoint) {
                $rowCount ++;

                if ($dataPoint->getConvertedAt()) {
                    $progressBar->update($rowCount);
                    continue;
                }

                if (strlen($dataPoint->getOldValue()) > 50000) {
                    $progressBar->update($rowCount);
                    continue;
                }

                $dataPoint->setNewValue($correctUtf8Encoding($dataPoint->getOldValue()));
                $dataPoint->setConvertedAt(new DateTime());
                if ($dataPoint->getOldValue() !== $dataPoint->getNewValue()) {
                    // Explicitly set approved only if the values do not match.
                    $dataPoint->setApproved(true);
                }
                $this->getObjectManager()->merge($dataPoint);

                try {
                    $this->getObjectManager()->flush();
                } catch (DriverException $e) {
                    $objectManagerConnection = $this->getObjectManager()->getConnection();
                    $objectManagerConfig = $this->getObjectManager()->getConfiguration();
                    $objectManager = $this->getObjectManager()->create($objectManagerConnection, $objectManagerConfig);

                    if ($forceConversion) {
                        $dataPoint->setNewValue(utf8_encode($dataPoint->getOldValue()));
                        $dataPoint->setConvertedAt(new DateTime());
                        $objectManager->merge($dataPoint);

                        try {
                            $objectManager->flush();
                        } catch (DriverException $e) {
                            die($e->getMessage());
                        }
                    } else {
                        $this->console->writeLine("Error converting DataPoint " . $dataPoint->getId(), Color::RED);
                        $dataPoint->setNewValue('');
                        $dataPoint->setErrored(true);
                        $objectManager->merge($dataPoint);
                        $objectManager->flush();
                    }
                }

                $progressBar->update($rowCount);
            }

            $this->getObjectManager()->clear();

            $page ++;
            $paginator->setCurrentPageNumber($page);
#            $progressBar->update($page);

            // Rebuild the paginator each iteration
            $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
            $paginator = new Paginator($adapter);
            $paginator->setItemCountPerPage($this->config['utf8-convert']['convert']['fetch-limit']);
            $paginator->setCurrentPageNumber($page);
        }

        $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $conversionName,
        ));

        $conversion->setEndAt(new DateTime());
        $this->getObjectManager()->flush();

        $progressBar->update($paginator->getTotalItemCount());

        return;
    }

    /**
     * Generate the SQL to run to correct the database
     */
    public function exportAction()
    {
        $conversionName = $this->params('name');

        $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->findOneBy([
            'name' => $conversionName
        ]);
        $errors = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->export($conversion, $this->database, $this->console);

        return array('conversion' => $conversion, 'errors' => $errors);
    }

    /**
     * Clone an existing conversion
     */
    public function cloneAction()
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $fromConversionName = $this->getRequest()->getParam('from');
        $toConversionName = $this->getRequest()->getParam('to');

        $fromConversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $fromConversionName
        ));

        if (!$fromConversion) {
            $this->console->write("The from conversion '" . $fromConversionName . "' was not found", Color::RED);
            return;
        }


        $this->console->writeLine("Cloning Conversion", Color::YELLOW);

        try {
            $conversion = new Entity\Conversion;
            $conversion->setCreatedAt($fromConversion->getCreatedAt());
            $conversion->setName($toConversionName);
            if ($fromConversion->getStartAt()) {
                $conversion->setStartAt($fromConversion->getStartAt());
                $conversion->setEndAt($fromConversion->getEndAt());
            }
            $this->getObjectManager()->persist($conversion);

            foreach ($fromConversion->getTableDef() as $table) {
                $table->addConversion($conversion);
                $conversion->addTableDef($table);
            }

            $this->getObjectManager()->flush();
            $conversionId = $conversion->getId();

        } catch (UniqueConstraintViolationException $e) {
            $this->console->write("The conversion name " . $toConversionName . " has already been used", Color::RED);
            return;
        }

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('dataPoint')
            ->from('Db\Entity\DataPoint', 'dataPoint')
            ->where($queryBuilder->expr()->eq('dataPoint.conversion', ':conversion'))
            ->setParameter('conversion', $fromConversion)
            ;

        $page = 0;
        $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($this->config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 0, $paginator->getTotalItemCount());

        $rowCount = 0;
        while (true) {
            foreach ($paginator as $dataPoint) {
                $rowCount ++;

                $progressBar->update($rowCount);

                $newDataPoint = new Entity\DataPoint();
                $newDataPoint
                    ->setColumnDef($dataPoint->getColumnDef())
                    ->setConversion($conversion)
                    ->setPrimaryKey($dataPoint->getPrimaryKey())
                    ->setOldValue($dataPoint->getOldValue())
                    ->setNewValue($dataPoint->getNewValue())
                    ->setComment($dataPoint->getComment())
                    ->setFlagged($dataPoint->getFlagged())
                    ->setApproved($dataPoint->getApproved())
                    ->setDenied($dataPoint->getDenied())
                    ->setImportedAt($dataPoint->getImportedAt())
                    ->setUser($dataPoint->getUser())
                    ;

                $this->getObjectManager()->persist($newDataPoint);

                foreach ($dataPoint->getDataPointPrimaryKeyDef() as $dataPointPrimaryKeyDef) {
                    $newDataPointPrimaryKeyDef = new Entity\DataPointPrimaryKeyDef();
                    $newDataPointPrimaryKeyDef
                        ->setDataPoint($newDataPoint)
                        ->setPrimaryKeyDef($dataPointPrimaryKeyDef->getPrimaryKeyDef())
                        ->setValue($dataPointPrimaryKeyDef->getValue())
                        ;

                    $this->getObjectManager()->persist($newDataPointPrimaryKeyDef);
                }
            }

            $this->getObjectManager()->flush();
            $this->getObjectManager()->clear();

            if ($rowCount >= $paginator->getTotalItemCount()) {
                break;
            }

            $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->find($conversionId);

            $page ++;
            $paginator->setCurrentPageNumber($page);
        }

        $this->console->writeLine('Conversion clone complete', Color::GREEN);
    }

    /**
     * Convert a single row to utf8 and interate until
     * there are no more conversions to be made
     */
    private function convertColumn(Entity\Conversion $conversion, Entity\TableDef $table, $row)
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $databaseConnection = $this->config['db']['adapters']['database'];

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
            $this->database,
            $resultSetPrototype
        );

        $page = 0;

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($this->config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        if ($paginator->getTotalItemCount()) {
            $this->console->writeLine($table->getName() . '.' . $row['COLUMN_NAME'], Color::YELLOW);
            $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 0, $paginator->getTotalItemCount());
        }

        $rowCount = 0;
        while (true) {
            foreach ($paginator as $utf8record) {
                $rowCount ++;

                $progressBar->update($rowCount);

                $column = $this->getObjectManager()->getRepository('Db\Entity\ColumnDef')->findOneBy(array(
                    'tableDef' => $table,
                    'name' => $row['COLUMN_NAME'],
                ));

                if (!$column) {
                    $column = new Entity\ColumnDef();
                    $column->setTableDef($table);
                    $column->setName($row['COLUMN_NAME']);

                    $columnDefinition = $this->informationSchema->query("
                        SELECT
                            COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH,
                            COLUMNS.IS_NULLABLE, COLUMNS.COLUMN_DEFAULT
                        FROM COLUMNS
                        WHERE COLUMNS.TABLE_SCHEMA = ?
                            AND COLUMNS.TABLE_NAME = ?
                            AND COLUMNS.COLUMN_NAME = ?
                   ", array($databaseConnection['database'], $row['TABLE_NAME'], $row['COLUMN_NAME']));

                    if (sizeof($columnDefinition) != 1) {
                        $this->console->write("Cannot fetch definition of " . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'], Color::RED);
                    }

                    foreach ($columnDefinition as $columnDef) {
                        $column->setDataType($columnDef['DATA_TYPE']);
                        $column->setLength($columnDef['CHARACTER_MAXIMUM_LENGTH']);
                        $column->setDefaultValue($columnDef['COLUMN_DEFAULT']);
                        $column->setIsNullable(($columnDef['IS_NULLABLE'] == 'YES'));
                        $column->setExtra($columnDef['EXTRA']);
                    }

                    $this->getObjectManager()->persist($column);
                    $this->getObjectManager()->flush();
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

                    $this->getObjectManager()->persist($dataPointPrimaryKey);
                }

                $this->getObjectManager()->persist($dataPoint);
            }

            $this->getObjectManager()->flush();
            $this->getObjectManager()->clear();
/**
 * this needs to be improved
 */
            // Re-fetch working entities
            $conversion = $this->getObjectManager()->getRepository('Db\Entity\Conversion')->find($conversionKey);
            $table = $this->getObjectManager()->getRepository('Db\Entity\TableDef')->find($tableKey);

            if ($rowCount >= $paginator->getTotalItemCount()) {
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
                $this->database,
                $resultSetPrototype
            );

            $page ++;
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($this->config['utf8-convert']['convert']['fetch-limit']);
        }
    }
}

