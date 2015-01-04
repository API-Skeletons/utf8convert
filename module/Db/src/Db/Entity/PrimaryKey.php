<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrimaryKey
 */
class PrimaryKey
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $column;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointPrimaryKey;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataPointPrimaryKey = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set table
     *
     * @param string $table
     * @return PrimaryKey
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return string 
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set column
     *
     * @param string $column
     * @return PrimaryKey
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get column
     *
     * @return string 
     */
    public function getColumn()
    {
        return $this->column;
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
     * Add dataPointPrimaryKey
     *
     * @param \Db\Entity\DataPointPrimaryKey $dataPointPrimaryKey
     * @return PrimaryKey
     */
    public function addDataPointPrimaryKey(\Db\Entity\DataPointPrimaryKey $dataPointPrimaryKey)
    {
        $this->dataPointPrimaryKey[] = $dataPointPrimaryKey;

        return $this;
    }

    /**
     * Remove dataPointPrimaryKey
     *
     * @param \Db\Entity\DataPointPrimaryKey $dataPointPrimaryKey
     */
    public function removeDataPointPrimaryKey(\Db\Entity\DataPointPrimaryKey $dataPointPrimaryKey)
    {
        $this->dataPointPrimaryKey->removeElement($dataPointPrimaryKey);
    }

    /**
     * Get dataPointPrimaryKey
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDataPointPrimaryKey()
    {
        return $this->dataPointPrimaryKey;
    }
}
