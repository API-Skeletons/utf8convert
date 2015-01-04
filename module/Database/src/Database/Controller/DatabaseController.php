<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class DatabaseController extends AbstractActionController
{
    public function truncateUtf8ConvertDatabaseAction()
    {
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $connection = $objectManager->getConnection();
        $platform   = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('DataPointPrimaryKeyDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('PrimaryKeyDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('DataPoint'));
        $connection->executeUpdate($platform->getTruncateTableSQL('ConversionToTableDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('ColumnDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('TableDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('Conversion'));
    }

    /**
     * Check MySQL utf8 settings for the target database
     */
    public function validateTargetDatabaseAction()
    {
        if (!$this->validateDatabaseSettings()) {
            // output sent in function
            return;
        }

        if (!$this->validateAllTablesAreUtf8()) {
            echo "\nOne or more tables are not utf8.  Run 'generate table conversion' and save the output to a shell script then run.\n";
            return;
        }

        if (!$this->validateAllColumnsAreUtf8()) {
            // output sent in function
            return;
        }

        echo "\nThe database has passed initial validation.\n";
    }

    /**
     * Create a shell script to convert tables to utf8
     */
    public function generateUtf8TableConversionAction()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $invalidTables = $informationSchema->query("
            SELECT TABLE_NAME, COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME
              FROM TABLES, COLLATION_CHARACTER_SET_APPLICABILITY
             WHERE COLLATION_CHARACTER_SET_APPLICABILITY.COLLATION_NAME = TABLES.TABLE_COLLATION
               AND TABLES.TABLE_SCHEMA = ?
               AND COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME != 'utf8'
        ", array($databaseConnection['database']));

        foreach ($invalidTables as $key => $value) {
            echo "mysqldump -u " . $databaseConnection['username'];
            if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                echo " -p" . $databaseConnection['password'];
            }
            if (isset($databaseConnection['host']) and $databaseConnection['host']) {
                echo " -h " . $databaseConnection['host'];
            }
            echo " --opt --skip-set-charset --skip-extended-insert --default-character-set=" . $value['CHARACTER_SET_NAME'];
            echo " " . $databaseConnection['database'] . " --tables " . $value['TABLE_NAME'] . " > utf8convert.table.sql;\n";
            echo "perl -i -pe 's/DEFAULT CHARSET=" . $value['CHARACTER_SET_NAME'] . "/DEFAULT CHARSET=utf8/' utf8convert.table.sql;\n";
            echo "mysql -u " . $databaseConnection['username'];
            if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                echo " -p" . $databaseConnection['password'];
            }
            if (isset($databaseConnection['host']) and $databaseConnection['host']) {
                echo " -h " . $databaseConnection['host'];
            }
            echo " " . $databaseConnection['database'] . " < utf8convert.table.sql;\n";
            echo "rm utf8convert.table.sql;\n\n";
        }
    }

    /**
     * Verify all table are utf8
     */
    private function validateAllTablesAreUtf8()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $invalidTables = $informationSchema->query("
            SELECT TABLE_NAME, COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME
              FROM TABLES, COLLATION_CHARACTER_SET_APPLICABILITY
             WHERE COLLATION_CHARACTER_SET_APPLICABILITY.COLLATION_NAME = TABLES.TABLE_COLLATION
               AND TABLES.TABLE_SCHEMA = ?
               AND COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME != 'utf8'
        ", array($databaseConnection['database']));

        if (sizeof($invalidTables)) {
            return false;
        }

        return true;
    }

    /**
     * Verify all columns are utf8
     */
    private function validateAllColumnsAreUtf8()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $invalidColumns = $informationSchema->query("
            SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND CHARACTER_SET_NAME IS NOT null
            AND CHARACTER_SET_NAME != 'utf8'
            AND COLLATION_NAME = 'utf8_general_ci'
        ", array($databaseConnection['database']));

        if (sizeof($invalidColumns)) {
            echo "\nThe following columns are of the wrong character set: \n\n";

            foreach ($invalidColumns as $row) {
                echo $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'] . "\n";
            }

            return false;
        }

        return true;
    }

    /**
     * Check the MySQL variables for this database
     */
    private function validateDatabaseSettings()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseVariables = $informationSchema->query("SHOW VARIABLES LIKE 'char%'")->execute();

        $utf8Settings = array(
            'character_set_client' => null,
            'character_set_connection' => null,
            'character_set_database' => null,
            'character_set_results' => null,
            'character_set_server' => null,
            'character_set_system' => null,
        );

        foreach ($databaseVariables as $key => $row) {
            if (in_array($row['Variable_name'], array_keys($utf8Settings))) {
                $utf8Settings[$row['Variable_name']] = $row['Value'];
            }
        }

        $success = true;
        foreach ($utf8Settings as $setting => $value) {
            if ($value !== 'utf8') {
                echo ("\nThe database variable $setting must be changed to 'utf8'\n");
                $success = false;
            }
        }

        return $success;
    }
}

