<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;
use Horde_Text_Diff_Renderer_Inline;

class DataController extends AbstractActionController
{
	public function validateAction()
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
		
		if ($success) {
			die('Validation Passed');
		}

		die('Validation Failed');
	}

    public function iterationAction()
    {
        $entity = $this->params()->fromRoute('entity');
        $field = $this->params()->fromRoute('field');
        $iteration = $this->params()->fromRoute('iteration');

        $database = $this->getServiceLocator()->get('database');

        $changes = $database->query("
            SELECT primaryKey, oldValue, newValue
              FROM Utf8Changes
              WHERE entity = ?
              AND field = ?
              AND iteration = ?
              AND newValue IS NOT NULL
          ORDER BY newValue
        ", array($entity, $field, $iteration));

        $renderer = new Horde_Text_Diff_Renderer_Inline();

        return array(
            'entity' => $entity,
            'field' => $field,
            'iteration' => $iteration,
            'data' => $changes,
            'renderer' => $renderer,
        );
    }

    public function indexAction()
    {
        $database = $this->getServiceLocator()->get('database');

        $cols = $database->query("
            SELECT entity, field, count(*) as changeCount
              FROM Utf8Changes
          GROUP BY entity, field
          ORDER BY entity, field
        ")->execute();

        $entities = array();
        foreach ($cols as $row) {
            $entityRow['entity'] = $row['entity'];
            $entityRow['field'] = $row['field'];
            $entityRow['total_data_points'] = $row['changeCount'];

            $entities[] = $entityRow;
        }

        foreach ($entities as $key => $row) {
            $erroredRows = $database->query("
                SELECT count(*) as data_point_errors
                FROM Utf8Changes
                WHERE entity = ?
                AND field = ?
                AND newValue like (concat('%', char(15712189), '%'))
                AND oldValue not like (concat('%', char(15712189), '%'))
            ", array($row['entity'], $row['field']));

            foreach ($erroredRows as $r) {
                $entities[$key]['data_point_errors'] = $r['data_point_errors'];
            }

            $unchangedRows = $database->query("
                SELECT count(*) as data_point_unchanged
                FROM Utf8Changes
                WHERE entity = ?
                AND field = ?
                AND newValue IS NULL
            ", array($row['entity'], $row['field']));

            foreach ($unchangedRows as $r) {
                $entities[$key]['data_point_unchanged'] = $r['data_point_unchanged'];
            }

            $iterations = $database->query("
                SELECT iteration, count(*) as changeCount
                FROM Utf8Changes
                WHERE entity = ?
                AND field = ?
                AND newValue IS NOT NULL
                GROUP BY entity, field, iteration
            ", array($row['entity'], $row['field']));

            $entities[$key]['iterations'] = array();
            foreach ($iterations as $iRow) {
                $dataPointErrors = 0;
                $errorRows = $database->query("
                    SELECT count(*) as data_point_errors
                    FROM Utf8Changes
                    WHERE entity = ?
                    AND field = ?
                    AND iteration = ?
                    AND newValue IS NOT NULL
                    AND newValue like (concat('%', char(15712189), '%'))
                ", array($row['entity'], $row['field'], $iRow['iteration']));

                foreach ($errorRows as $r) {
                    $dataPointErrors = $r['data_point_errors'];
                }

                $entities[$key]['iterations'][] = array(
                    'index' => $iRow['iteration'],
                    'data_point_changes' => $iRow['changeCount'],
                    'data_point_errors' => $dataPointErrors,
                );
            }
        }

        return new ViewModel(array('entities' => $entities));
    }
}
