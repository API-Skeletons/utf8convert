<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface as Color;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

final class TruncateController extends AbstractConsoleController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    public function __construct($objectManager, ConsoleAdapterInterface $console)
    {
        $this->setObjectManager($objectManager);
        $this->setConsole($console);
    }

    public function truncateUtf8ConvertDatabaseAction()
    {
        $connection = $this->getObjectManager()->getConnection();
        $platform   = $connection->getDatabasePlatform();

        // Cannot truncate a table with foreign key constraints.
        $connection->executeUpdate($platform->getTruncateTableSQL('ConversionToTableDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('DataPointPrimaryKeyDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('PrimaryKeyDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('DataPoint'));
        $connection->executeUpdate($platform->getTruncateTableSQL('ColumnDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('TableDef'));
        $connection->executeUpdate($platform->getTruncateTableSQL('Conversion'));

        $this->console->writeLine("utf8convert database has been truncated", Color::GREEN);
    }
}

