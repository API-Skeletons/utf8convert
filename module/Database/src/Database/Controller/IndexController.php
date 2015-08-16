<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class IndexController extends AbstractActionController
{
    public function createAdministratorAction()
    {
        $email = $this->getRequest()->getParam('email');
        $displayName = $this->getRequest()->getParam('displayName');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $zfcUserService = $this->getServiceLocator()->get('zfcuser_user_service');

        $password = 'u' . substr(hash('sha512', rand()), 0, 10);
        $data = array(
            'display_name' => $displayName,
            'email' => $email,
            'password' => $password,
            'passwordVerify' => $password,
        );

        $user = $zfcUserService->register($data);

        if (!$user) {
            echo "\nFailed to create administrator\n";
            return;
        }

        $user = $objectManager->getRepository('Db\Entity\User')->find($user->getId());
        $administratorRole = $objectManager->getRepository('Db\Entity\Role')->findOneBy(array(
            'roleId' => 'administrator'
        ));

        $user->addRole($administratorRole);
        $administratorRole->addUser($user);

        $objectManager->flush();

        echo "\nCreated Administrator: " . $user->getEmail() . " " . $password . "\n";
        return;
    }

    /**
     * Check MySQL utf8 settings for the database
     */
    public function validateAction()
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
     * Refactor the database so all
        varchar, char, enum columns are varchar(255)
        *text columns are longtext
     */
    public function refactorAction()
    {
        $consoleWhitelist = $this->getRequest()->getParam('whitelist');
        $consoleBlacklist = $this->getRequest()->getParam('blacklist');

        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $refactorDataTypes = $this->getServiceLocator()->get('Config');
        $refactorDataTypes = $refactorDataTypes['utf8-convert']['refactor']['data-types'];

        $informationSchema->query("SET session wait_timeout=86400")->execute();

        if (!$refactorDataTypes) {
            echo "\nNo data types to refactor have been defined\n";
            return;
        }

        $blacklistTables = '';
        if ($consoleBlacklist) {
            $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", explode(',', $consoleBlacklist))
                . "')";
        } else {
            if (isset($config['utf8-convert']['refactor']['blacklist-tables']) and $config['utf8-convert']['convert']['blacklist-tables']) {
                $blacklistTables = "AND COLUMNS.TABLE_NAME NOT IN ('" . implode("', '", $config['utf8-convert']['blacklist-tables'])
                    . "')";
            }
        }

        $whitelistTables= '';
        if ($consoleWhitelist) {
            $whitelistTables = "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", explode(',', $consoleWhitelist))
                . "')";
        } else {
            if (isset($config['utf8-convert']['refactor']['whitelist-tables']) and $config['utf8-convert']['convert']['whitelist-tables']) {
                $whitelistTables= "AND COLUMNS.TABLE_NAME IN ('" . implode("', '", $config['utf8-convert']['whitelist-tables'])
                    . "')";
            }
        }

        $refactorColumns = $informationSchema->query("
            SELECT COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME,
                COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH,
                COLUMNS.IS_NULLABLE, COLUMNS.COLUMN_DEFAULT
            FROM COLUMNS, TABLES
            WHERE COLUMNS.TABLE_SCHEMA = ?
            AND COLUMNS.TABLE_NAME = TABLES.TABLE_NAME
            AND TABLES.TABLE_SCHEMA = COLUMNS.TABLE_SCHEMA
            AND TABLES.TABLE_TYPE = 'BASE TABLE'
            AND COLUMNS.DATA_TYPE IN ('" . implode("', '", array_keys($refactorDataTypes)) . "')
            $blacklistTables
            $whitelistTables
            ORDER BY COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME
        ", array($databaseConnection['database']));

        $refactorTables = array();
        foreach ($refactorColumns as $row) {
            $refactorTables[$row['TABLE_NAME']][] = $row;
        }

        foreach ($refactorTables as $table => $rows) {
            $this->refactorTable($rows);
        }

        echo "\nRefactoring finished\n";
    }

    private function refactorTable($rows)
    {
        $database = $this->getServiceLocator()->get('database');
        $refactorDataTypes = $this->getServiceLocator()->get('Config');
        $refactorDataTypes = $refactorDataTypes['utf8-convert']['refactor']['data-types'];

        $sql = array();
        foreach ($rows as $row) {
            if (!in_array($row['DATA_TYPE'], array_keys($refactorDataTypes))) {
                die("Unmapped data type found: " . $row['DATA_TYPE']);
            }
            $sqlLine = "MODIFY `" . $row['COLUMN_NAME'] . '` ' . $refactorDataTypes[$row['DATA_TYPE']] . ' ';

            if ($row['IS_NULLABLE'] == 'NO') {
                $sqlLine .= ' NOT NULL ';
            }
            if (!is_null($row['COLUMN_DEFAULT'])) {
                $sqlLine .= ' DEFAULT ' . $database->getPlatform()->quoteValue($row['COLUMN_DEFAULT']) . ' ';
            }
            if ($row['EXTRA']) {
                $sqlLine .= $row['EXTRA'];
            }

            $sql[] = $sqlLine;
        }

        if ($sql) {
            $command = "ALTER TABLE `" . $row['TABLE_NAME'] . "` " . implode(', ', $sql);
            echo $command . ";\n\n";

            try {
                $database->query($command)->execute();
            } catch (RuntimeException $e) {
                die("\n\n" . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Convert all data in all string columns to utf8 by correcting encoding
     */
    public function convertAction()
    {
        $consoleWhitelist = $this->getRequest()->getParam('whitelist');
        $consoleBlacklist = $this->getRequest()->getParam('blacklist');
        $clearLog = $this->getRequest()->getParam('clear-log');

        $informationSchema = $this->getServiceLocator()->get('information-schema');
        $databaseConnection = $this->getServiceLocator()->get('Config');
        $databaseConnection = $databaseConnection['db']['adapters']['database'];

        $config = $this->getServiceLocator()->get('Config');

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

        $convertColumns = $informationSchema->query("
            SELECT COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME, COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH
            FROM COLUMNS, TABLES
            WHERE COLUMNS.TABLE_SCHEMA = ?
            AND COLUMNS.TABLE_NAME = TABLES.TABLE_NAME
            AND TABLES.TABLE_SCHEMA = COLUMNS.TABLE_SCHEMA
            AND TABLES.TABLE_TYPE = 'BASE TABLE'
            AND COLUMNS.DATA_TYPE IN ('varchar', 'longtext')
            $blacklistTables
            $whitelistTables
            ORDER BY COLUMNS.TABLE_NAME, COLUMNS.COLUMN_NAME
        ", array($databaseConnection['database']));

        if ($clearLog) {
            $this->deleteUtf8ConvertTable();
        }

        foreach ($convertColumns as $row) {
            $primaryKeys = $informationSchema->query("
                SELECT COLUMN_NAME, COLUMN_KEY
                  FROM COLUMNS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND column_key = 'PRI'
              ORDER BY COLUMN_NAME
            ", array($databaseConnection['database'], $row['TABLE_NAME']));

            $primaryKey = null;
            $keys = array();
            if (sizeof($primaryKeys)) {
                foreach ($primaryKeys as $primaryKeyColumn) {
                    $keys[] = $primaryKeyColumn['COLUMN_NAME'];
                }
                if (!$keys) {
                    die('Table ' . $row['TABLE_NAME'] . ' has no primary key');
                }

                $primaryKey = 'concat(`' . implode('`, `', $keys) . '`)';
            }

            $this->convertRow($row, $primaryKey);
        }

        echo "\nutf8 conversion complete\n";
    }

    /**
     * Convert a single row to utf8 and interate until
     * there are no more conversions to be made
     */
    private function convertRow($row, $primaryKey)
    {
        $dirtyData = true;
        $iteration = 0;
        $database = $this->getServiceLocator()->get('database');
        $refactorDataTypes = $this->getServiceLocator()->get('Config');
        $refactorDataTypes = $refactorDataTypes['utf8-convert']['refactor']['data-types'];

        echo "\n" . $row['TABLE_NAME'] . ' ' . $row['COLUMN_NAME'] . "\n";

        while ($dirtyData) {
            $iteration ++;
            try {
                $command = "DROP TEMPORARY TABLE temptable";
                $database->query($command)->execute();
            } catch (RuntimeException $e) {
                // table does not exist
            }

            $sql = "CREATE TEMPORARY TABLE temptable (SELECT * FROM `"
                . $row['TABLE_NAME'] . "` WHERE length(`"
                . $row['COLUMN_NAME'] . "`) != char_length(`"
                . $row['COLUMN_NAME'] . "`))";

            $database->query($sql)->execute();

            $sql = "INSERT INTO Utf8Convert (entity, field, iteration, oldValue, primaryKey) "
                . " select '" . $row['TABLE_NAME'] . "', '" . $row['COLUMN_NAME']
                . "', " . $iteration . ", " . "`" . $row['COLUMN_NAME'] . "`, "
                . $primaryKey . " FROM temptable";

            $database->query($sql)->execute();

            $sql = "alter table temptable modify `" . $row['COLUMN_NAME'] . "` " . $refactorDataTypes[$row['DATA_TYPE']] . " character set latin1";
            $database->query($sql)->execute();

            $sql = "alter table temptable modify `" . $row['COLUMN_NAME'] . "` blob";
            $database->query($sql)->execute();

            $sql = "alter table temptable modify `" . $row['COLUMN_NAME'] . "` " . $refactorDataTypes[$row['DATA_TYPE']] . " character set utf8";
            $database->query($sql)->execute();

            $sql = "DELETE FROM temptable WHERE length(`"
                . $row['COLUMN_NAME'] . "`) = char_length(`"
                . $row['COLUMN_NAME'] . "`)";
            $database->query($sql)->execute();

            $sql = "
                UPDATE Utf8Convert SET newValue = (
                    SELECT `" . $row['COLUMN_NAME'] . "`
                    FROM temptable
                    WHERE Utf8Convert.primaryKey = " . $primaryKey . "
                    )
                WHERE newValue IS NULL
                AND Utf8Convert.entity = '" . $row['TABLE_NAME'] . "'
                AND Utf8Convert.field = '" . $row['COLUMN_NAME'] . "'
            ";
            $database->query($sql)->execute();

            $sql = "REPLACE INTO `" . $row['TABLE_NAME'] . "` (select * from temptable)";
            $result = $database->query($sql)->execute();

            if (!$result->getAffectedRows()) {
                $dirtyData = false;
            }

            $command = "DROP TEMPORARY TABLE temptable";
            $database->query($command)->execute();

            echo "Replaced " . $result->getAffectedRows() . " rows on iteration $iteration\n";
        }
    }

    /**
     * Delete the Utf8Convert table
     */
    private function deleteUtf8ConvertTable()
    {
        $database = $this->getServiceLocator()->get('database');

        $sql = "DROP TABLE Utf8Convert";

        try {
            $database->query($sql)->execute();
        } catch (RuntimeException $e) {
            // table didn't exist
        }

        return true;
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

