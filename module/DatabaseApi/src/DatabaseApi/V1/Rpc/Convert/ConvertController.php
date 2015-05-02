<?php
namespace DatabaseApi\V1\Rpc\Convert;

use Zend\Mvc\Controller\AbstractActionController;
use Db\Entity;

class ConvertController extends AbstractActionController
{
    public function convertAction()
    {
        $data = $this->bodyParams();
        $value = $data['value'];

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $database = $this->getServiceLocator()->get('database');

        $objectManager->getRepository('Db\Entity\ConvertWorker')->truncate();

        $worker = new Entity\ConvertWorker();
        $worker->setValue($value);
        $objectManager->persist($worker);
        $objectManager->flush();
        $workerId = $worker->getId();
        $objectManager->clear();

        $objectManager->getRepository('Db\Entity\ConvertWorker')->utf8Convert();
        $worker = $objectManager->getRepository('Db\Entity\ConvertWorker')->find($workerId);
        $objectManager->getRepository('Db\Entity\ConvertWorker')->truncate();

        return array('value' => $worker->getValue());
    }
}
