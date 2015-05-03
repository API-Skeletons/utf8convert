<?php
namespace DatabaseApi\V1\Rpc\Url;

class UrlControllerFactory
{
    public function __invoke($controllers)
    {
        return new UrlController();
    }
}
