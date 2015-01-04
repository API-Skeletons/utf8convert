<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ColumnDef
 */
class ColumnDef
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $dataType;

    /**
     * @var string
     */
    private $length;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var boolean
     */
    private $isNullable;

    /**
     * @var string
     */
    private $extra;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPoint;

    /**
     * @var \Db\Entity\TableDef
     */
    private $tableDef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataPoint = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ColumnDef
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set dataType
     *
     * @param string $dataType
     * @return ColumnDef
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string 
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set length
     *
     * @param string $length
     * @return ColumnDef
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return string 
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     * @return ColumnDef
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return string 
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set isNullable
     *
     * @param boolean $isNullable
     * @return ColumnDef
     */
    public function setIsNullable($isNullable)
    {
        $this->isNullable = $isNullable;

        return $this;
    }

    /**
     * Get isNullable
     *
     * @return boolean 
     */
    public function getIsNullable()
    {
        return $this->isNullable;
    }

    /**
     * Set extra
     *
     * @param string $extra
     * @return ColumnDef
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return string 
     */
    public function getExtra()
    {
        return $this->extra;
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
     * Add dataPoint
     *
     * @param \Db\Entity\DataPoint $dataPoint
     * @return ColumnDef
     */
    public function addDataPoint(\Db\Entity\DataPoint $dataPoint)
    {
        $this->dataPoint[] = $dataPoint;

        return $this;
    }

    /**
     * Remove dataPoint
     *
     * @param \Db\Entity\DataPoint $dataPoint
     */
    public function removeDataPoint(\Db\Entity\DataPoint $dataPoint)
    {
        $this->dataPoint->removeElement($dataPoint);
    }

    /**
     * Get dataPoint
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDataPoint()
    {
        return $this->dataPoint;
    }

    /**
     * Set tableDef
     *
     * @param \Db\Entity\TableDef $tableDef
     * @return ColumnDef
     */
    public function setTableDef(\Db\Entity\TableDef $tableDef)
    {
        $this->tableDef = $tableDef;

        return $this;
    }

    /**
     * Get tableDef
     *
     * @return \Db\Entity\TableDef 
     */
    public function getTableDef()
    {
        return $this->tableDef;
    }
}
