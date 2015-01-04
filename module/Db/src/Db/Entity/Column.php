<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Column
 */
class Column
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
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPoint;

    /**
     * @var \Db\Entity\Table
     */
    private $table;

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
     * @return Column
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
     * @return Column
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
     * @return Column
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
     * @return Column
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
     * Set table
     *
     * @param \Db\Entity\Table $table
     * @return Column
     */
    public function setTable(\Db\Entity\Table $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return \Db\Entity\Table 
     */
    public function getTable()
    {
        return $this->table;
    }
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
     * Set defaultValue
     *
     * @param string $defaultValue
     * @return Column
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
     * @return Column
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
     * @return Column
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
}
