<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace Phpro\DoctrineHydrationModule\Hydrator;

use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class DoctrineHydrator
 *
 * @package Phpro\DoctrineHydrationModule\Hydrator
 */
class DoctrineHydrator
    implements HydratorInterface
{
    /**
     * @var HydratorInterface
     */
    protected $extractService;

    /**
     * @var HydratorInterface
     */
    protected $hydrateService;

    /**
     * @param $extractService
     * @param $hydrateService
     */
    public function __construct($extractService, $hydrateService)
    {
        $this->extractService = $extractService;
        $this->hydrateService = $hydrateService;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getExtractService()
    {
        return $this->extractService;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrateService()
    {
        return $this->hydrateService;
    }

    /**
     * Extract values from an object
     *
     * @param object $object
     *
     * @return array
     */
    public function extract($object)
    {
        return $this->extractService->extract($object);
    }

     /**
      * Hydrate $object with the provided $data.
      *
      * @param array  $data
      * @param object $object
      *
      * @return object
      */
     public function hydrate(array $data, $object)
     {
         // Zend hydrator:
        if ($this->hydrateService instanceof HydratorInterface) {
            return $this->hydrateService->hydrate($data, $object);
        }

        // Doctrine hydrator: (parameters switched)
        return $this->hydrateService->hydrate($object, $data);
     }
}
