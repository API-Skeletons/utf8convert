<?php
namespace DatabaseApi\V1\Rpc\Convert;

class ConvertControllerFactory
{
    public function __invoke($controllers)
    {
        return new ConvertController();
    }
}
