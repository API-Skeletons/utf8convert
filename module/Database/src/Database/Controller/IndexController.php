<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class IndexController extends AbstractActionController
{
    public function indexAction()
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

    public function generateUtf8TableConversionAction()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config')['db']['adapters']['database'];

        $invalidTables = $informationSchema->query("
            SELECT TABLE_NAME, COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME
              FROM TABLES, COLLATION_CHARACTER_SET_APPLICABILITY
             WHERE COLLATION_CHARACTER_SET_APPLICABILITY.COLLATION_NAME = TABLES.TABLE_COLLATION
               AND TABLES.TABLE_SCHEMA = ?
               AND COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME != 'utf8'
        ", [$databaseConnection['database']]);

        foreach ($invalidTables as $key => $value) {
            echo "mysqldump -u " . $databaseConnection['username'];
            if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                echo " -p" . $databaseConnection['password'];
            }
            echo " --opt --skip-set-charset --skip-extended-insert --default-character-set=" . $value['CHARACTER_SET_NAME'];
            echo " " . $databaseConnection['database'] . " --tables " . $value['TABLE_NAME'] . " > utf8convert.table.sql;\n";
            echo "perl -i -pe 's/DEFAULT CHARSET=" . $value['CHARACTER_SET_NAME'] . "/DEFAULT CHARSET=utf8/' utf8convert.table.sql;\n";
            echo "mysql -u " . $databaseConnection['username'];
            if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                echo " -p" . $databaseConnection['password'];
            }
            echo " " . $databaseConnection['database'] . " < utf8convert.table.sql;\n\n";
        }
    }

    /**
     * Refactor the database so all
        varchar and char columns are varchar(255)
        *text columns are longtext
        integer and tinyint and smallint, mediumint columns are bigint unsigned;
     */
    public function refactorAction()
    {
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config')['db']['adapters']['database'];

        $refactorColumns = $informationSchema->query("
            SELECT COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME, COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH
            FROM COLUMNS, TABLES
            WHERE COLUMNS.TABLE_SCHEMA = ?
            AND COLUMNS.TABLE_NAME = TABLES.TABLE_NAME
            AND TABLES.TABLE_SCHEMA = COLUMNS.TABLE_SCHEMA
            AND TABLES.TABLE_TYPE = 'BASE TABLE'
            AND COLUMNS.DATA_TYPE IN ('varchar', 'char', 'text', 'mediumtext')
            ORDER BY COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME
        ", [$databaseConnection['database']]);

        foreach ($refactorColumns as $row) {
            $table = $row['TABLE_NAME'];
            $column = $row['COLUMN_NAME'];
            echo "$table $column ";
            echo "FROM " . $row['DATA_TYPE'];
            echo "\n";

            $this->refactorRow($row);
        }

        die("\nRefactoring finished\n");
    }

    public function refactorRow($row)
    {
        $database = $this->getServiceLocator()->get('database');

        switch ($row['DATA_TYPE']) {
            case 'varchar':
            case 'char':
                if ($row['CHARACTER_MAXIMUM_LENGTH'] == 255 and $row['DATA_TYPE'] != 'char') {
                    return true;
                }

                $database->query("ALTER TABLE `" . $row['TABLE_NAME'] . "` MODIFY `" . $row['COLUMN_NAME'] . '` varchar(255) ' . $row['EXTRA'])
                    ->execute();
                break;
/*
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
                $database->query("ALTER TABLE `" . $row['TABLE_NAME'] . "` MODIFY `" . $row['COLUMN_NAME'] . '` bigint ' . $row['EXTRA'])
                    ->execute();
                break;
*/
            case 'text':
            case 'mediumtext':
                $database->query("ALTER TABLE `" . $row['TABLE_NAME'] . "` MODIFY `" . $row['COLUMN_NAME'] . '` longtext ' . $row['EXTRA'])
                    ->execute();
                break;

            default:
                break;
        }
    }

    public function convertAction()
    {
        $database = $this->getServiceLocator()->get('database');
    }

    private function convertRow()
    {

    }

    private function generateUtf8ChangesSQL()
    {
        $sql = "
        create table Utf8Changes (
            id int not null primary key auto_increment,
            entity varchar(255),
            field varchar(255),
            primaryKey int,
            iteration int,
            oldValue longtext,
            newValue longtext
        );
        ";

        return $sql;
    }

    private function validateAllTablesAreUtf8()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config')['db']['adapters']['database'];

        $invalidTables = $informationSchema->query("
            SELECT TABLE_NAME, COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME
              FROM TABLES, COLLATION_CHARACTER_SET_APPLICABILITY
             WHERE COLLATION_CHARACTER_SET_APPLICABILITY.COLLATION_NAME = TABLES.TABLE_COLLATION
               AND TABLES.TABLE_SCHEMA = ?
               AND COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME != 'utf8'
        ", [$databaseConnection['database']]);

        if (sizeof($invalidTables)) {
            return false;
        }

        return true;
    }


    private function validateAllColumnsAreUtf8()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config')['db']['adapters']['database'];

        $invalidColumns = $informationSchema->query("
            SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND CHARACTER_SET_NAME IS NOT null
            AND CHARACTER_SET_NAME != 'utf8'
            AND COLLATION_NAME = 'utf8_general_ci'
        ", [$databaseConnection['database']]);

        if (sizeof($invalidColumns)) {
            echo "\nThe following columns are of the wrong character set: \n\n";

            foreach ($invalidColumns as $row) {
                echo $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'] . "\n";
            }

            return false;
        }

        return true;
    }

    private function validateDatabaseSettings()
    {
        // Validate all database settings are utf8
        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseVariables = $informationSchema->query("SHOW VARIABLES LIKE 'char%'")->execute();

        $utf8Settings = [
            'character_set_client' => null,
            'character_set_connection' => null,
            'character_set_database' => null,
            'character_set_results' => null,
            'character_set_server' => null,
            'character_set_system' => null,
        ];

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

