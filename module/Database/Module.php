<?php

namespace Database;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

class Module implements ConsoleUsageProviderInterface, ConsoleBannerProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'validate' => 'Validate the database is configured for utf8',
            'generate table conversion' => 'Create a shell script to move all non-utf8 tables to utf8',
            'refactor [--whitelist=table,list] [--blacklist=table,list]' => 'Refactor database character fields to varchar(255) and text to longtext',
            'convert [--whitelist=table,list] [--blacklist=table,list] [--clear-log]' => 'Fix utf8 data in tables and columns'
        );
    }

    public function getConsoleBanner(Console $console)
    {
        return 'Stuki Org Utf8Convert';
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'database' => function (ServiceManager $serviceManager)
                {
                    $config = $serviceManager->get('Config');
                    $config = $config['db']['adapters']['database'];
                    $adapter = new Adapter($config);

                    return $adapter;
                },
                'information-schema' => function (ServiceManager $serviceManager)
                {
                    $config = $serviceManager->get('Config');
                    $config = $config['db']['adapters']['information-schema'];
                    $adapter = new Adapter($config);

                    return $adapter;
                }
            ),
        );
    }
}
