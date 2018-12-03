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
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\DBAL\Exception\DriverException;

use Db\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use Zend\Console\Adapter\Posix;
use Zend\ProgressBar\Adapter\Console as ProgressBarConsoleAdaper;
use Zend\ProgressBar\ProgressBar;
use DateTime;

class ConversionController extends AbstractActionController
{
    /**
     * Convert all data in all string columns to utf8 by correcting encoding
     */
    public function createAction()
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $console = $this->getServiceLocator()->get('Console');

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

        $console->writeLine("Creating Conversion", Color::CYAN);

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

        $console->writeLine("Conversion created", Color::GREEN);
    }

    /**
     * Run a conversion
     */
    public function convertAction()
    {
        if (! $this->getRequest() instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $console = $this->getServiceLocator()->get('Console');

        $conversionName = $this->getRequest()->getParam('name');
        $forceConversion = $this->getRequest()->getParam('force');

        $config = $this->getServiceLocator()->get('Config');
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
            $console->writeLine("Conversion $conversionName cannot be found.  Use --conversion=name", Color::RED);
            return;
        }

        $conversion->setStartAt(new DateTime());
        $objectManager->flush();

        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder->select('dataPoint')
            ->from('Db\Entity\DataPoint', 'dataPoint')
            ->andwhere($queryBuilder->expr()->eq('dataPoint.conversion', ':conversion'))
            ->setParameter('conversion', $conversion)
            ->addOrderBy('dataPoint.id', 'DESC')
#            ->andWhere('dataPoint.id = 51267')
            ;

        $page = 1;
        $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        $console->writeLine("Converting " . $conversion->getName(), Color::CYAN);
        $console->writeLine($paginator->getTotalItemCount() . " DataPoints", Color::YELLOW);
        $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 1, $paginator->count());

        $rowCount = 0;
        while ($page <= $paginator->count()) {
#            echo "Set page: $page, rowCount: $rowCount, Total Pages: " . $paginator->count() . "\n";

            foreach ($paginator as $dataPoint) {
                $rowCount ++;

                if ($dataPoint->getConvertedAt()) {
                    continue;
                }

                if (strlen($dataPoint->getOldValue()) > 50000) {
                    continue;
                }

#echo mb_strlen($dataPoint->getOldValue()) . ' ' . $dataPoint->getId() . "\n";
                $newValue = $this->convertToUtf8($dataPoint->getOldValue(), $dataPoint);
#                if ($dataPoint->getOldValue() === $newValue) {
#                    die('utf8 found matching old and new values');
#                }

                $dataPoint->setNewValue($newValue);
                $dataPoint->setConvertedAt(new DateTime());
                $objectManager->merge($dataPoint);

                try {
                    $objectManager->flush();
                } catch (DriverException $e) {
                    $objectManagerConnection = $objectManager->getConnection();
                    $objectManagerConfig = $objectManager->getConfiguration();
                    $objectManager = $objectManager->create($objectManagerConnection, $objectManagerConfig);

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
                        $console->writeLine("Error converting DataPoint " . $dataPoint->getId(), Color::RED);
                        $dataPoint->setNewValue('');
                        $dataPoint->setErrored(true);
                        $objectManager->merge($dataPoint);
                        $objectManager->flush();
                    }
                }
            }

            $objectManager->clear();

            $page ++;
            $paginator->setCurrentPageNumber($page);
            $progressBar->update($page);

            // Rebuild the paginator each iteration
            $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
            $paginator = new Paginator($adapter);
            $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
            $paginator->setCurrentPageNumber($page);
        }

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $conversionName,
        ));

        $conversion->setEndAt(new DateTime());
        $objectManager->flush();

        $progressBar->update($paginator->getTotalItemCount());

        return;
    }

    /**
     * Generate the SQL to run to correct the database
     */
    public function exportAction()
    {
        $conversionName = $this->params('name');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $database = $this->getServiceLocator()->get('database');
        $console = $this->getServiceLocator()->get('Console');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy([
            'name' => $conversionName
        ]);
        $errors = $objectManager->getRepository('Db\Entity\Conversion')->import($conversion, $database, $console);

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

        $console = $this->getServiceLocator()->get('Console');

        $fromConversionName = $this->getRequest()->getParam('from');
        $toConversionName = $this->getRequest()->getParam('to');

        $config = $this->getServiceLocator()->get('Config');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $fromConversion = $objectManager->getRepository('Db\Entity\Conversion')->findOneBy(array(
            'name' => $fromConversionName
        ));

        if (!$fromConversion) {
            $console->write("The from conversion '" . $fromConversionName . "' was not found", Color::RED);
            return;
        }


        $console->writeLine("Cloning Conversion", Color::CYAN);

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
            $conversionId = $conversion->getId();

        } catch (UniqueConstraintViolationException $e) {
            $console->write("The conversion name " . $toConversionName . " has already been used", Color::RED);
            return;
        }

        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder->select('dataPoint')
            ->from('Db\Entity\DataPoint', 'dataPoint')
            ->where($queryBuilder->expr()->eq('dataPoint.conversion', ':conversion'))
            ->setParameter('conversion', $fromConversion)
            ;

        $page = 0;
        $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
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

                $objectManager->persist($newDataPoint);

                foreach ($dataPoint->getDataPointPrimaryKeyDef() as $dataPointPrimaryKeyDef) {
                    $newDataPointPrimaryKeyDef = new Entity\DataPointPrimaryKeyDef();
                    $newDataPointPrimaryKeyDef
                        ->setDataPoint($newDataPoint)
                        ->setPrimaryKeyDef($dataPointPrimaryKeyDef->getPrimaryKeyDef())
                        ->setValue($dataPointPrimaryKeyDef->getValue())
                        ;

                    $objectManager->persist($newDataPointPrimaryKeyDef);
                }
            }

            $objectManager->flush();
            $objectManager->clear();

            if ($rowCount >= $paginator->getTotalItemCount()) {
                break;
            }

            $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionId);

            $page ++;
            $paginator->setCurrentPageNumber($page);
        }

        $console->write('Conversion clone complete', Color::GREEN);
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

        $console = $this->getServiceLocator()->get('Console');

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

        $page = 0;

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        if ($paginator->getTotalItemCount()) {
            $console->writeLine($table->getName() . '.' . $row['COLUMN_NAME'], Color::YELLOW);
            $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 0, $paginator->getTotalItemCount());
        }

        $rowCount = 0;
        while (true) {
            foreach ($paginator as $utf8record) {
                $rowCount ++;

                $progressBar->update($rowCount);

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
                        $console->write("Cannot fetch definition of " . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'], Color::RED);
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
                $database,
                $resultSetPrototype
            );

            $page ++;
            $paginator = new Paginator($paginatorAdapter);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        }
    }

    /**
     * Given a valid or invalid UTF8 string parse it and convert
     * all UTF8 sequences into UTF8 characters while leaving valid
     * UTF8 characters alone.
     */
    private function convertToUtf8($input, Entity\DataPoint $dataPoint, $counter = 0)
    {
        $length = mb_strlen($input);
        $return = '';
        $character = '';
        $showProgress = false;

        if ($length > 20000) {
            $showProgress = true;
#            $progressBar = new ProgressBar(new ProgressBarConsoleAdaper(), 1, $length);
        }
#        $progressLength = 1;

        // Chunk string into working strings
        // mb_ functions are slow on large strings
        $chunkSize = 100;
        $stringChunks = mb_split('', $input, $chunkSize);

        $chunkCount = sizeof($stringChunks);
        $workingString = array_shift($stringChunks);
        while (mb_strlen($workingString)) {
            // If the working string is < 100 add the next chunk to the string
            if (mb_strlen($workingString) < 10) {
                if ($stringChunks) {
                    $workingString .= array_shift($stringChunks);
                }
            }

            // Fetch one UTF8 character
            $character = mb_substr($workingString, 0, 1, 'UTF-8');

            $characterUtf32BE= @mb_convert_encoding($character, 'UTF-32BE', 'UTF-8');
            if (!$characterUtf32BE) {
                die ('invalid character on data point' . $dataPoint->getId() . "\n" . $character . "\n" . $input);
                // \xF5\x8C\xAB\xBA
            }
            $characterCode = hexdec(bin2hex($characterUtf32BE));

            $bytes = 1;
            $multibyte = false;

            // If this character defines bytes then it
            // could be a correct character or it could
            // be the start of an invalid sequence.
            if ($characterCode >= 0xf0 && $characterCode <= 0xf7) {
                $bytes = 4;
            } else if ($characterCode >= 0xe0 && $characterCode <= 0xef) {
                $bytes = 3;
            } else if ($characterCode >= 0xc0 && $characterCode <= 0xdf) {
                $bytes = 2;
            }

            // Convert invalid chars to utf8
            $i = 0;
            $characterLength = 1;
            while ($bytes > 1) {
                $i++;

                $nextCharacter = mb_substr($workingString, $i, 1, 'UTF-8');
                $nextCharacterUtf32BE = mb_convert_encoding($nextCharacter, 'UTF-32BE', 'UTF-8');
                $nextCharacterCode = hexdec(bin2hex($nextCharacterUtf32BE));

                // Does the original character stands alone and is not an invalid byte sequence?
                if ($nextCharacterCode < 0x80) {
                    // Yes, stand alone.  $character is correctly encoded and $nextCharacter is re-enqueued
                    // to be correctly encoded too.  This character is within the byte range of the
                    // first were the first invalid.
                    break;
                }

                $character .= $nextCharacter;
                $multibyte = true;
                $bytes--;
            }

            if ($multibyte) {
                // Try Windows-125X charsets
                $newUtf8Char = @mb_convert_encoding($character, 'Windows-1252');
                $checkUtf8Char = @mb_substr($newUtf8Char, 0, 1, 'UTF-8');
                $characterLength = mb_strlen($character);

                if ($checkUtf8Char) {
                    // Successfully restored a Windwows-125x character
                    $character = mb_convert_encoding($newUtf8Char, 'UTF-8');
                } else {
                    // Check UTF8

                    // If these functions fail then the derived encoding found is invalid
                    // and the string is one or more valid utf8 characters together
                    $newUtf8Char = @mb_convert_encoding($character, "ISO-8859-1");
                    $checkUtf8Char = @mb_substr($newUtf8Char, 0, 1, 'UTF-8');
                    $characterLength = mb_strlen($character);

                    if ($checkUtf8Char) {
                        // Successfully restored a UTF8 character
                        $newUtf8Char = @mb_convert_encoding($newUtf8Char, 'UTF-8');

                        if ($newUtf8Char) {
                            $character = $newUtf8Char;
                            $workingString = mb_substr($workingString, mb_strlen($newUtf8Char) - 1);

                            $characterLength = mb_strlen($character);
                        } else {
                            die("Error \n$string\n[$newUtf8Char] \n");
                        }
                    }
                }
            }

            // $character may be multiple characters by this point
            $workingString = mb_substr($workingString, $characterLength);
            $return .= $character;

            if ($showProgress) {
#                $progressBar->update($progressLength += mb_strlen($character));
            }
        }

        // Re-run to fix double or more encodings

#        if (isset($progressBar)) {
#            $progressBar->update($length);
#        }

        if ($input !== $return) {
            $counter ++;

            if ($counter > 5) {
                die("$return \n $input \n break because 5 iterations of convertToUtf8 were ran.");
            }

            return $this->convertToUtf8($return, $dataPoint, $counter);
        }

        return $return;
    }
}

