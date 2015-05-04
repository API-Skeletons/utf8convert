<?php
namespace DatabaseApi\V1\Rest\DataPoint;

use ZF\Apigility\Doctrine\Server\Resource\DoctrineResource;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Db\Entity;
use Pusher;

class DataPointResource extends DoctrineResource implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $objectManager = $this->getObjectManager();

        $fromDataPoint = $objectManager->getRepository('Db\Entity\DataPoint')->find($data->fromDataPointId);

        $column = $objectManager->getRepository('Db\Entity\ColumnDef')->findOneBy(array(
            'tableDef' => $fromDataPoint->getColumnDef()->getTableDef(),
            'name' => $data->column
        ));

        // Create a new column def
        if (!$column) {
            $informationSchema = $this->getServiceLocator()->get('information-schema');

            $config = $this->getServiceLocator()->get('Config');
            $databaseConnection = $config['db']['adapters']['database'];

            $column = new Entity\ColumnDef();
            $column->setTableDef($fromDataPoint->getColumnDef()->getTableDef());
            $column->setName($data->column);

            $columnDefinition = $informationSchema->query("
                SELECT
                    COLUMNS.DATA_TYPE, COLUMNS.EXTRA, COLUMNS.CHARACTER_MAXIMUM_LENGTH,
                    COLUMNS.IS_NULLABLE, COLUMNS.COLUMN_DEFAULT
                FROM COLUMNS
                WHERE COLUMNS.TABLE_SCHEMA = ?
                    AND COLUMNS.TABLE_NAME = ?
                    AND COLUMNS.COLUMN_NAME = ?
            ", array($databaseConnection['database'], $fromDataPoint->getColumnDef()->getTableDef()->getName(), $data->column));

            if (sizeof($columnDefinition) != 1) {
                echo "\nCannot fetch definition of " . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'] . "\n";
            }

            foreach ($columnDefinition as $columnDef) {
                $column->setDataType($columnDef['DATA_TYPE']);
                $column->setLength($columnDef['CHARACTER_MAXIMUM_LENGTH']);
                $column->setDefaultValue($columnDef['COLUMN_DEFAULT']);
                $column->setIsNullable(($columnDef['IS_NULLABLE'] == 'YES'));
                $column->setExtra($columnDef['EXTRA']);
            }

            $objectManager->persist($column);
        }

        $dataPoint = new Entity\DataPoint();
        $dataPoint->setColumnDef($column);
        $dataPoint->setConversion($fromDataPoint->getConversion());
        $dataPoint->setOldValue($data->oldValue);
        $dataPoint->setNewValue($data->newValue);
        $dataPoint->setFlagForReview(false);
        $dataPoint->setPrimaryKey($fromDataPoint->getPrimaryKey());

        foreach ($fromDataPoint->getDataPointPrimaryKey() as $copyFromDataPointPrimaryKey) {
            $dataPointPrimaryKey = new Entity\DataPointPrimaryKeyDef();
            $dataPointPrimaryKey->setPrimaryKeyDef($copyFromDataPointPrimaryKey->getPrimaryKeyDef());
            $dataPointPrimaryKey->setDataPoint($dataPoint);
            $dataPointPrimaryKey->setValue($copyFromDataPointPrimaryKey->getValue());

            $objectManager->persist($dataPointPrimaryKey);
        }

        $objectManager->persist($dataPoint);
        $objectManager->flush();

        return $dataPoint;
    }

    public function patch($id, $data)
    {
        $dataPoint = parent::patch($id, $data);

        $app_id = '118458';
        $app_key = '4a692f8bd0d32221b070';
        $app_secret = 'b85af6f07d3b7c888a28';

        $pusher = new Pusher($app_key, $app_secret, $app_id);
        $pusher->trigger('column', 'update', $dataPoint->getArrayCopy());

        return $dataPoint;
    }
}
