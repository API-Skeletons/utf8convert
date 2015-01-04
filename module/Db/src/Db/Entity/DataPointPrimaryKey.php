<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataPointPrimaryKey
 */
class DataPointPrimaryKey
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
     * @var \Db\Entity\PrimaryKey
     */
    private $primaryKey;


    /**
     * Set value
     *
     * @param string $value
     * @return DataPointPrimaryKey
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
     * @return DataPointPrimaryKey
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

    /**
     * Set primaryKey
     *
     * @param \Db\Entity\PrimaryKey $primaryKey
     * @return DataPointPrimaryKey
     */
    public function setPrimaryKey(\Db\Entity\PrimaryKey $primaryKey = null)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Get primaryKey
     *
     * @return \Db\Entity\PrimaryKey 
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
}
