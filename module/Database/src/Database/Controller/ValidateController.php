<?php

namespace Database\Controller;

use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;

final class ValidateController extends AbstractConsoleController
{
    private $database;
    private $informationSchema;
    private $config;

    public function __construct(
        DbAdapter $database,
        DbAdapter $informationSchema,
        array $config,
        ConsoleAdapterInterface $console)
    {
        $this->database = $database;
        $this->informationSchema = $informationSchema;
        $this->config = $config;
        $this->console = $console;
    }

    /**
     * Check MySQL utf8mb4 settings for the database
     */
    public function validateAction()
    {
        if (! $this->validateDatabaseSettings()) {
            // output sent in function
            $this->console->writeLine("One or more database settings have errors.", Color::RED);

            return;
        }

        $invalidTables = $this->validateAllTablesAreUtf8mb4();
        if ($invalidTables) {
            foreach ($invalidTables as $t) {
                $this->console->writeLine($t['TABLE_NAME'], Color::YELLOW);
            }
            $this->console->writeLine("The table(s) listed above are not utf8mb4", Color::RED);
            $confirm = new Prompt\Confirm('Would you like to print the SQL to correct these?');
            $result = $confirm->show();

            if ($result == 1) {
                foreach ($invalidTables as $t) {
                    $this->console->writeLine("ALTER TABLE `" . $t['TABLE_NAME'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                }
            }

            return;
        }

        if (! $this->validateAllColumnsAreUtf8mb4()) {
            // output sent in function
            return;
        }

        $this->console->writeLine('The database has passed validation', Color::GREEN);
    }

    /**
     * Check the MySQL variables for this database
     */
    private function validateDatabaseSettings()
    {
        // Validate all database settings are utf8
        $databaseVariables = $this->informationSchema->query("SHOW VARIABLES LIKE 'char%'")->execute();
;
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
            switch($setting) {
                case 'character_set_system':
                    if ($value === 'utf8') {
                        continue;
                    }

                    $this->console->writeLine(
                        "The server variable `character_set_system` must "
                        . "be changed to 'utf8'", Color::YELLOW);
                    $success = false;
                    break;
                case 'character_set_database':
                    if ($value === 'utf8') {
                        continue;
                    }

                    $this->console->writeLine(
                        "The database variable $setting must "
                        . "be changed to 'utf8mb4'", Color::YELLOW);
                    $this->console->writeLine("    Run this command on your MySQL server to correct this: ", Color::YELLOW);
                    $databaseName = $this->config['db']['adapters']['database']['database'];
                    $this->console->writeLine("    ALTER DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;", Color::CYAN);


                    break;
                default:
                    if ($value === 'utf8mb4') {
                        continue;
                    }

                    $this->console->writeLine(
                        "The database variable $setting must "
                        . "be changed to 'utf8mb4'", Color::YELLOW);
                    $success = false;
            }
        }

        return $success;
    }

    /**
     * Verify all tables are utf8mb4
     */
    private function validateAllTablesAreUtf8mb4()
    {
        // Validate all database settings are utf8
        $databaseConnection = $this->config['db']['adapters']['database'];

        $invalidTables = $this->informationSchema->query("
            SELECT TABLE_NAME, COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME
              FROM TABLES, COLLATION_CHARACTER_SET_APPLICABILITY
             WHERE COLLATION_CHARACTER_SET_APPLICABILITY.COLLATION_NAME = TABLES.TABLE_COLLATION
               AND TABLES.TABLE_SCHEMA = ?
               AND COLLATION_CHARACTER_SET_APPLICABILITY.CHARACTER_SET_NAME != 'utf8mb4'
        ", array($databaseConnection['database']));

        if (sizeof($invalidTables)) {
            return $invalidTables;
        }

        return false;
    }

    /**
     * Verify all columns are utf8
     */
    private function validateAllColumnsAreUtf8mb4()
    {
        // Validate all database settings are utf8
        $databaseConnection = $this->config['db']['adapters']['database'];

        $invalidColumns = $this->informationSchema->query("
            SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND CHARACTER_SET_NAME IS NOT null
            AND CHARACTER_SET_NAME != 'utf8'
            AND COLLATION_NAME = 'utf8_general_ci'
        ", array($databaseConnection['database']));

        if (sizeof($invalidColumns)) {
            $this->console->writeLine("The following columns are of the wrong character set:", Color::RED);

            foreach ($invalidColumns as $row) {
                $this->console->writeLine($row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'], Color::YELLOW);
            }

            return false;
        }

        return true;
    }
}

