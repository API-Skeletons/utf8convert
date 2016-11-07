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
            'administrator:create --email=email@net --displayName=administrator' => 'Create the administrator for this instance',

            'database:validate' => 'Validate the database is configured for utf8',
            'database:generate:utf8-tables' => 'Create a shell script to move all non-utf8 tables to utf8 for non-valid database',
            'database:truncate' => 'Remove all conversion data from the conversion database',

            'conversion:create [--name=conversionName] [--whitelist=] [--blacklist=]' => 'Create a new conversion',
            'conversion:convert --name=' => 'Run the initial conversion of utf8 multiple encodings',
            'conversion:export --name=' => 'Export the SQL result of the conversion',
            'conversion:clone [--from=] [--to=]' => 'Create a clone of a conversion',
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
