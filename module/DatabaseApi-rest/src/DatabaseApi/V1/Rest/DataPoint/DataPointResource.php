<?php
namespace DatabaseApi\V1\Rest\DataPoint;

use ZF\ApiProblem\ApiProblem;
use Application\Rest\AbstractResourceListener;
use ZF\Apigility\Doctrine\Server\Paginator\Adapter\DoctrineOrmAdapter;

use Zend\Paginaotr\Adapter\Callback;
use Zend\Paginator\Paginator as ZendPaginator;
use ZF\Hal\Entity as HalEntity;
use ZF\Hal\Collection;
use ZF\Hal\Entity;
use ZF\ContentNegotiation\ViewModel as ContentNegotiationViewModel;

class DataPointResource extends AbstractResourceListener
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        $objectManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $conversion = $objectManager->getRepository('Db\Entity\Conversion')->find($params['conversion']);
        $column = $objectManager->getRepository('Db\Entity\ColumnDef')->find($params['column']);

        $qb = $objectManager->createQueryBuilder();
        $qb->select('dp')
            ->from('Db\Entity\DataPoint', 'dp')
            ->andwhere('dp.conversion = :conversion')
            ->andwhere('dp.columnDef = :columm')
            ->setParameter('conversion', $conversion)
            ->setParameter('columm', $column)
            ->orderBy('dp.primaryKey', 'ASC')
            ;

        $page = (isset($params['page'])) ? $params['page']: 1;
#die($page . ' = page');
        $adapter = new DoctrineOrmAdapter($qb->getQuery(), false);

        $paginator = new ZendPaginator($adapter);
        $paginator->setItemCountPerPage(50);
        $paginator->setCurrentPageNumber($page);

        $hal = new Collection($paginator);
        $hal->setCollectionName('dataPoint');
        $hal->setCollectionRoute('database-api.rest.data-point');
        $hal->setCollectionRouteOptions(array(
            'query' => array(
                'conversion' => $conversion,
                'column' => $column,
            ),
        ));
        $hal->setPage($paginator->getCurrentPageNumber());
        $hal->setPageSize($paginator->getItemCountPerPage());

        $viewModel = new ContentNegotiationViewModel(array(
            'payload' => $hal
        ));

        return $viewModel;
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
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
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
