<?php
namespace DatabaseApi\V1\Rpc\Url;

use Zend\Mvc\Controller\AbstractActionController;

class UrlController extends AbstractActionController
{
    public function urlAction()
    {
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $data = $this->bodyParams();

        $dataPoint = $objectManager->getRepository('Db\Entity\DataPoint')->find($data['dataPointId']);
        $url = $objectManager->getRepository('Db\Entity\TableDef')->url($dataPoint);

        return array('url' => $url);
    }
}
