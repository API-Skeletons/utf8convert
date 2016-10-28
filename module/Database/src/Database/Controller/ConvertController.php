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
            INSERT INTO DataPoint (conversion_id, column_def_id, primaryKey, oldValue, newValue, comment, flagged, approved, denied, importedAt, user_id)
                SELECT " . $conversion->getId() . ", column_def_id, primaryKey, oldValue, newValue, comment, flagged, approved, denied, importedAt, user_id
                FROM DataPoint
                WHERE conversion_id = " . $fromConversion->getId() . "
        ");

        echo "\nConversion copy complete\n";
    }

    public function runConversionAction()
    {
        $conversionName = $this->getRequest()->getParam('name');

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
            echo "Conversion $conversionName cannot be found.  Use --conversion=name\n";
            return;
        }

        if ($conversion->getStartAt()) {
            echo "This conversion has already been ran\n";
            return;
        }

        $conversion->setStartAt(new DateTime());
        $objectManager->flush();
// asdf
        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder->select('dataPoint')
            ->from('Db\Entity\DataPoint', 'dataPoint')
            ->where($queryBuilder->expr()->eq('dataPoint.conversion', ':conversion'))
            ->setParameter('conversion', $conversion)
            ;

        $page = 1;
        $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage($config['utf8-convert']['convert']['fetch-limit']);
        $paginator->setCurrentPageNumber($page);

        $rowCount = 0;
        while (true) {
            foreach ($paginator as $dataPoint) {
                $rowCount ++;

                $dataPoint->setNewValue($this->convertToUtf8($dataPoint->getOldValue()));
            }

            $objectManager->flush();

            if ($rowCount == $paginator->getTotalItemCount()) {
                break;
            }

            $page ++;
            $paginator->setCurrentPageNumber($page);
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

    /**
     * Given a valid or invalid UTF8 string parse it and convert
     * all UTF8 sequences into UTF8 characters while leaving valid
     * UTF8 characters alone.
     */
    private function convertToUtf8($input, $counter = 0)
    {
        $length = mb_strlen($input);
        $return = '';

        for($i = 0; $i < $length; $i++) {
echo "$i of $length \n";

            // Fetch one UTF8 character
            $character = mb_substr($input, $i, 1, 'UTF-8');
            $characterUtf32BE= mb_convert_encoding($character, 'UTF-32BE', 'UTF-8');
            $characterCode = hexdec(bin2hex($characterUtf32BE));
            $bytes = 1;
            $multibyte = false;

            // If this character defines bytes then it
            // could be a correct character or it could
            // be the start of an invalid sequence.
            if ($characterCode >= 0xF0 && $characterCode <= 0xF7) {
                $bytes = 4;
            } else if ($characterCode >= 0xE0 && $characterCode <= 0xEF) {
                $bytes = 3;
            } else if ($characterCode >= 0xC0 && $characterCode <= 0xDF) {
                $bytes = 2;
            }

            // Convert invalid chars to utf8
            while ($bytes > 1) {
                $i++;

                $nextCharacter = mb_substr($input, $i, 1, 'UTF-8');
                $nextCharacterUtf32BE = mb_convert_encoding($nextCharacter, 'UTF-32BE', 'UTF-8');
                $nextCharacterCode = hexdec(bin2hex($nextCharacterUtf32BE));

                // Does the original character stands alone and is not an invalid byte sequence?
                if ($nextCharacterCode < 0x80) {
                    // Yes, stand alone.  $character is correctly encoded and $nextCharacter is re-enqueued
                    // to be correctly encoded too.  This character is within the byte range of the
                    // first were the first invalid.
                    $i--;
                    break;
                }

                $character .= $nextCharacter;

                $multibyte = true;
                $bytes--;
            }

            if ($multibyte) {
                // If these functions fail then the derived encoding found is invalid
                // and the string is one or more valid utf8 characters together
                $newUtf8Char = @mb_convert_encoding($character, "ISO-8859-1");
                $checkUtf8Char = @mb_substr($newUtf8Char, 0, 1, 'UTF-8');

                if ($checkUtf8Char) {
                    // Successfully restored a UTF8 character
                    $character = mb_convert_encoding($newUtf8Char, 'UTF-8');
                } else {
                    // Special case handling for Windows-125X charsets
                    $specialCharacter = mb_substr($character, 0, 1, 'UTF-8');
                    $specialCharacterUtf32BE = mb_convert_encoding($specialCharacter, 'UTF-32BE', 'UTF-8');
                    $specialCharacterCode = hexdec(bin2hex($specialCharacterUtf32BE));

                    if ($specialCharacterCode == 0xE2) {
                        $specialCharacter2 = mb_substr($character, 1, 1, 'UTF-8');
                        $specialCharacterUtf32BE2 = mb_convert_encoding($specialCharacter2, 'UTF-32BE', 'UTF-8');
                        $specialCharacterCode2 = hexdec(bin2hex($specialCharacterUtf32BE2));

                        if ($specialCharacterCode2 == 0x20AC) {
                            $specialCharacter3 = mb_substr($character, 2, 1, 'UTF-8');
                            $specialCharacterUtf32BE3 = mb_convert_encoding($specialCharacter3, 'UTF-32BE', 'UTF-8');
                            $specialCharacterCode3 = hexdec(bin2hex($specialCharacterUtf32BE3));

                            switch ($specialCharacterCode3) {
                                case 0xA6:
                                    $character = 0x2026; // elipsies
                                    break;
                                case 0x201D:
                                case 0x201C:
                                    $character = 0x2014; // long hyphen
                                    break;
                                case 0xA8:
                                    $character = 0x2013; // short hyphen
                                    break;
                                case 0x2DC:
                                    $character = 0x2018; // left single quotation mark
                                    break;
                                case 0x2122:
                                    $character = 0x2019; // right single quotation mark
                                    break;
                                case 0xA1:
                                    $character = 0x2021; // double plus? vertical align ++
                                    break;
                                case 0x153:
                                    $character = 0x201C; // double curly open quote
                                    break;
                                case 0x9D:
                                case 0xA0:
                                    $character = 0x201D; // double curly close quote
                                    break;
                                case 0xA2:
                                    $character = 0x2022;
                                    break;
                                default:
                                    die($input . "\n" . $character . ' ' . $specialCharacterCode3);
                                    break;
                            }

                            $character = mb_convert_encoding($newUtf8Char, 'UTF-8');
                        }
                    }
                }
            }

            // $character may be multiple characters by this point
            $return .= $character;
        }

        // Re-run to fix double or more encodings
        if ($input != $return) {
            $counter ++;

            if ($counter > 2) {
                die("$return $input break because than 3 iterations were ran.");
            }

            return $this->convertToUtf8($return, $counter);
        }

        echo $return . "\n";

        return $return;
    }
}
