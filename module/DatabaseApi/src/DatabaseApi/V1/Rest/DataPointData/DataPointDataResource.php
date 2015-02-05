<?php
namespace DatabaseApi\V1\Rest\DataPointData;

use ZF\ApiProblem\ApiProblem;
use Application\Rest\AbstractResourceListener;

class DataPointDataResource extends AbstractResourceListener
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $objectManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $database = $this->getServiceManager()->get('database');

        $dataPoint = $objectManager->getRepository('Db\Entity\DataPoint')->find($id);

        $keys = array();
        foreach ($dataPoint->getDataPointPrimaryKey() as $dataPointPrimaryKey) {
            $keys[] = $database->getPlatform()->quoteIdentifier($dataPointPrimaryKey->getPrimaryKeyDef()->getName()) . ' = ' .
                $database->getPlatform()->quoteValue($dataPointPrimaryKey->getValue());
        }


        // Fetch all datapoints for the target row
        $lastDataPointPrimaryKey = null;
        foreach ($dataPoint->getDataPointPrimaryKey() as $dataPointPrimaryKey) {
            $allDataPointPrimaryKey = $objectManager
                ->getRepository('Db\Entity\DataPointPrimaryKeyDef')
                ->findBy(array(
                    'primaryKeyDef' => $dataPointPrimaryKey->getPrimaryKeyDef(),
                    'value' => $dataPointPrimaryKey->getValue(),
                ));

            if ($lastDataPointPrimaryKey) {
                foreach ($allDataPointPrimaryKey as $entity) {
                    if (!$lastDataPointPrimaryKey->contains($entity)) {
                        $allDataPointPrimaryKey->remove($entity);
                    }
                }
            }

            $lastDataPointPrimaryKey = $allDataPointPrimaryKey;
        }


        $sql = "SELECT * FROM "
            . $dataPoint->getColumnDef()->getTableDef()->getName()
            . " WHERE "
            . implode(',', $keys)
            ;

        $result = $database->query($sql)->execute();

        foreach ($result as $row) {

            foreach ($row as $key => $value) {
                foreach ($lastDataPointPrimaryKey as $entity) {
                    if ($entity->getDataPoint()->getColumnDef()->getName() == $key) {
                        $row[$key] = $entity->getDataPoint();
                    }
                }
            }

            return $row;
        }
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        $objectManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $database = $this->getServiceManager()->get('database');

        $dataPoint = $objectManager->getRepository('Db\Entity\DataPoint')->find($id);

        $keys = array();
        foreach ($dataPoint->getDataPointPrimaryKey() as $dataPointPrimaryKey) {
            $keys[] = $database->getPlatform()->quoteIdentifier($dataPointPrimaryKey->getPrimaryKeyDef()->getName()) . ' = ' .
                $database->getPlatform()->quoteValue($dataPointPrimaryKey->getValue());
        }

         $sql = "UPDATE "
            . $database->getPlatform()->quoteIdentifier($dataPoint->getColumnDef()->getTableDef()->getName())
            . " SET "
            . $database->getPlatform()->quoteIdentifier($data->column)
            . ' = '
            . $database->getPlatform()->quoteValue($data->value)
            . " WHERE "
            . implode(',', $keys)
            ;

        $database->query($sql)->execute();

        return $this->fetch($id);
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
