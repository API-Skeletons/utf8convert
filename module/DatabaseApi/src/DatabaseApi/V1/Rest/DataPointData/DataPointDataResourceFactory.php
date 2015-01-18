<?php
namespace DatabaseApi\V1\Rest\DataPointData;

class DataPointDataResourceFactory
{
    public function __invoke($services)
    {
        return new DataPointDataResource();
    }
}
