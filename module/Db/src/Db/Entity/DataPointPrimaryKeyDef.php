<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataPointPrimaryKeyDef
 */
class DataPointPrimaryKeyDef
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
     * @var \Db\Entity\PrimaryKeyDef
     */
    private $primaryKeyDef;


    /**
     * Set value
     *
     * @param string $value
     * @return DataPointPrimaryKeyDef
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
     * @return DataPointPrimaryKeyDef
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

    /**
     * Set primaryKeyDef
     *
     * @param \Db\Entity\PrimaryKeyDef $primaryKeyDef
     * @return DataPointPrimaryKeyDef
     */
    public function setPrimaryKeyDef(\Db\Entity\PrimaryKeyDef $primaryKeyDef)
    {
        $this->primaryKeyDef = $primaryKeyDef;

        return $this;
    }

    /**
     * Get primaryKeyDef
     *
     * @return \Db\Entity\PrimaryKeyDef 
     */
    public function getPrimaryKeyDef()
    {
        return $this->primaryKeyDef;
    }
}
