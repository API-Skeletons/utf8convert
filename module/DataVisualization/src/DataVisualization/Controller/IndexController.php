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

        return new ViewModel(array(
            'conversions' => $conversions
        ));
    }

    public function conversionAction()
    {
        $id = $this->params()->fromRoute('conversion_id');

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($id);

        return new ViewModel(array(
            'conversion' => $conversion,
        ));
    }
}

