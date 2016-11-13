<?php

namespace DataVisualization\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $conversions = $objectManager->getRepository('Db\Entity\Conversion')->findBy(
            array(),
            array('createdAt' => 'DESC')
        );

        foreach ($conversions as $conversion) {
            $objectManager->getRepository('Db\Entity\Conversion')->setDataPointCount($conversion);
        }

        $tables = $objectManager->getRepository('Db\Entity\TableDef')->findBy(array(), array('name' => 'ASC'));

        return new ViewModel(array(
            'conversions' => $conversions,
            'tables' => $tables,
        ));
    }

    public function tableAction()
    {
        $id = $this->params()->fromRoute('table_id');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $table = $objectManager->getRepository('Db\Entity\TableDef')->find($id);

        if ($this->getRequest()->isPost()) {
            $table->setUrl($this->getRequest()->getPost('url'));

            $objectManager->flush();

            return $this->plugin('redirect')->toRoute('visualization');
        }

        return new ViewModel(array(
            'table' => $table
        ));
    }

    public function conversionAction()
    {
        set_time_limit(0);
        $id = $this->params()->fromRoute('conversion_id');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($id);

        return new ViewModel(array(
            'conversion' => $conversion,
        ));
    }

    public function columnAction()
    {
        $conversionId = $this->params()->fromRoute('conversion_id');
        $columnId = $this->params()->fromRoute('column_id');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionId);
        $column = $objectManager->getRepository('Db\Entity\ColumnDef')->find($columnId);

        return new ViewModel(array(
            'conversion' => $conversion,
            'column' => $column,
        ));
    }

    public function searchAction()
    {
        $conversionId = $this->params()->fromRoute('conversion_id');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($conversionId);

        return new ViewModel(array(
            'conversion' => $conversion,
        ));
    }
}

