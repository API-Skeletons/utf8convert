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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $table;


    /**
     * Add table
     *
     * @param \Db\Entity\Table $table
     * @return Conversion
     */
    public function addTable(\Db\Entity\Table $table)
    {
        $this->table[] = $table;

        return $this;
    }

    /**
     * Remove table
     *
     * @param \Db\Entity\Table $table
     */
    public function removeTable(\Db\Entity\Table $table)
    {
        $this->table->removeElement($table);
    }

    /**
     * Get table
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTable()
    {
        return $this->table;
    }
}
