<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $this->validateDatabaseSettings();
        $this->validateAllTablesAreUtf8();


        $db = $this->getServiceLocator()->get('database');

        die('SUCCESS');

        return new ViewModel();
    }

    public function validateAllTablesAreUtf8()
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
            echo "Some or all of your database tables are not in utf8.  ";
            echo "Correct this by copying the following to a shell script and running from the command line: ";
            echo '<pre>';
            foreach ($invalidTables as $key => $value) {
                echo "mysqldump -u " . $databaseConnection['username'];
                if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                    echo " -p" . $databaseConnection['password'];
                }
                echo " --opt --skip-set-charset --skip-extended-insert --default-character-set" . $value['CHARACTER_SET_NAME'];
                echo " " . $databaseConnection['database'] . " --tables " . $value['TABLE_NAME'] . " > convert.table.sql;\n";
                echo "perl -i -pe 's/DEFAULT CHARSET=" . $value['CHARACTER_SET_NAME'] . "/' convert.table.sql;\n";
                echo "mysql -u " . $databaseConnection['username'];
                if (isset($databaseConnection['password']) and $databaseConnection['password']) {
                    echo " -p" . $databaseConnection['password'];
                }
                echo " " . $databaseConnection['database'] . " < convert.table.sql;\n\n";
            }

            echo '</pre>';
            die();
#mysqldump -u root --opt --skip-set-charset   --default-character-set=latin1 --skip-extended-insert   etree --tables artist_aliases > etree.table.sql;
#perl -i -pe 's/DEFAULT CHARSET=latin1/DEFAULT CHARSET=utf8/'   etree.table.sql;
#mysql -u root etree < etree.table.sql;
        }

        $invalidColumns = $informationSchema->query("
            SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND CHARACTER_SET_NAME IS NOT null
            AND CHARACTER_SET_NAME != 'utf8'
            AND COLLATION_NAME = 'utf8_general_ci'
        ", [$databaseConnection['database']]);

        if (sizeof($invalidColumns)) {
            echo "The following columns are of the wrong character set: ";
            foreach ($invalidColumns as $key => $value) {
                echo "$key => "; print_r($value);
            }
            die();
       }
   }

    public function validateDatabaseSettings()
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

        foreach ($utf8Settings as $setting => $value) {
            if ($value !== 'utf8') {
                die("The database variable $setting must be changed to 'utf8'");
            }
        }
    }
}

