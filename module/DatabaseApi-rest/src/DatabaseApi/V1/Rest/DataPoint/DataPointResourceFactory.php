<?php
namespace DatabaseApi\V1\Rest\DataPoint;

class DataPointResourceFactory
{
    public function __invoke($services)
    {
        return new DataPointResource();
    }
}
