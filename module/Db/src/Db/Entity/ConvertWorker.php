<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConvertWorker
 */
class ConvertWorker
{
    /**
     * @var string
     */
    private $value;

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
     * @return ConvertWorker
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
     * @return ConvertWorker
     */
    public function setDataPoint(\Db\Entity\DataPoint $dataPoint)
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
