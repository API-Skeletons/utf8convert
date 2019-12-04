<?php

namespace Database;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface, ConsoleBannerProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'database:validate' => 'Validate the database is configured for utf8',

# Drop your database and recreate it instead.
#            'database:truncate' => 'Remove all conversion data from the conversion database',

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
