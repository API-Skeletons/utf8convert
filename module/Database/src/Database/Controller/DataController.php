<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class DataController extends AbstractActionController
{
    public function indexAction()
    {
        $database = $this->getServiceLocator()->get('database');

        $tables = $database->query("
            SELECT entity, count(*) as rowCount
              FROM Utf8Changes
          GROUP BY entity
          ORDER BY entity
        ")->execute();

        return new ViewModel(['tables' => $tables]);
    }
}

