<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrimaryKeyDef
 */
class PrimaryKeyDef
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointPrimaryKey;

    /**
     * @var \Db\Entity\TableDef
     */
    private $tableDef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataPointPrimaryKey = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PrimaryKeyDef
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
     * @param \Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKey
     * @return PrimaryKeyDef
     */
    public function addDataPointPrimaryKey(\Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKey)
    {
        $this->dataPointPrimaryKey[] = $dataPointPrimaryKey;

        return $this;
    }

    /**
     * Remove dataPointPrimaryKey
     *
     * @param \Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKey
     */
    public function removeDataPointPrimaryKey(\Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKey)
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

    /**
     * Set tableDef
     *
     * @param \Db\Entity\TableDef $tableDef
     * @return PrimaryKeyDef
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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointPrimaryKeyDef;


    /**
     * Add dataPointPrimaryKeyDef
     *
     * @param \Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKeyDef
     *
     * @return PrimaryKeyDef
     */
    public function addDataPointPrimaryKeyDef(\Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKeyDef)
    {
        $this->dataPointPrimaryKeyDef[] = $dataPointPrimaryKeyDef;

        return $this;
    }

    /**
     * Remove dataPointPrimaryKeyDef
     *
     * @param \Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKeyDef
     */
    public function removeDataPointPrimaryKeyDef(\Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKeyDef)
    {
        $this->dataPointPrimaryKeyDef->removeElement($dataPointPrimaryKeyDef);
    }

    /**
     * Get dataPointPrimaryKeyDef
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDataPointPrimaryKeyDef()
    {
        return $this->dataPointPrimaryKeyDef;
    }
}
