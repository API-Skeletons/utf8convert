<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataPointIteration
 */
class DataPointIteration
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $iteration;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Db\Entity\DataPoint
     */
    private $dataPoint;


    /**
     * Set value
     *
     * @param string $value
     * @return DataPointIteration
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set iteration
     *
     * @param integer $iteration
     * @return DataPointIteration
     */
    public function setIteration($iteration)
    {
        $this->iteration = $iteration;

        return $this;
    }

    /**
     * Get iteration
     *
     * @return integer 
     */
    public function getIteration()
    {
        return $this->iteration;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dataPoint
     *
     * @param \Db\Entity\DataPoint $dataPoint
     * @return DataPointIteration
     */
    public function setDataPoint(\Db\Entity\DataPoint $dataPoint = null)
    {
        $this->dataPoint = $dataPoint;

        return $this;
    }

    /**
     * Get dataPoint
     *
     * @return \Db\Entity\DataPoint 
     */
    public function getDataPoint()
    {
        return $this->dataPoint;
    }
}
