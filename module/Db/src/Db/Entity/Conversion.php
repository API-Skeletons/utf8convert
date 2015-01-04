<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Conversion
 */
class Conversion
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $completedAt;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPoint;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tableDef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataPoint = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tableDef = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Conversion
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
     * Set description
     *
     * @param string $description
     * @return Conversion
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Conversion
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set completedAt
     *
     * @param \DateTime $completedAt
     * @return Conversion
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get completedAt
     *
     * @return \DateTime 
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
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
     * @return Conversion
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
     * Add tableDef
     *
     * @param \Db\Entity\TableDef $tableDef
     * @return Conversion
     */
    public function addTableDef(\Db\Entity\TableDef $tableDef)
    {
        $this->tableDef[] = $tableDef;

        return $this;
    }

    /**
     * Remove tableDef
     *
     * @param \Db\Entity\TableDef $tableDef
     */
    public function removeTableDef(\Db\Entity\TableDef $tableDef)
    {
        $this->tableDef->removeElement($tableDef);
    }

    /**
     * Get tableDef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTableDef()
    {
        return $this->tableDef;
    }
}
