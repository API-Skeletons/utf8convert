<?php

namespace Application\Rest;

use ZF\Rest\AbstractResourceListener as ZendAbstractResourceListener;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class AbstractResourceListener extends ZendAbstractResourceListener implements ServiceManagerAwareInterface
{
    protected $serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}