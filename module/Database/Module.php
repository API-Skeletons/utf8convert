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
#            'administrator:create --email=email@net --displayName=administrator' => 'Create the administrator for this instance',

            'database:validate' => 'Validate the database is configured for utf8',
            'database:truncate' => 'Remove all conversion data from the conversion database',

            'database:generate:utf8mb4-tables' => 'Create a shell script to move all non-utf8 tables to utf8mb4 for non-valid database',

            'conversion:create [--name=conversionName] [--whitelist=] [--blacklist=]' => 'Create a new conversion',
            'conversion:convert --name=' => 'Run the initial conversion of utf8 multiple encodings',
            'conversion:export --name=' => 'Run the SQL result of the conversion against the target database',

            'conversion:clone [--from=] [--to=]' => 'Create a clone of a conversion',
        );
    }

    public function getConsoleBanner(Console $console)
    {
        return 'API Skeletons - Utf8Convert';
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
