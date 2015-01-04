<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataPoint
 */
class DataPoint
{
    /**
     * @var string
     */
    private $primaryKey;

    /**
     * @var string
     */
    private $oldValue;

    /**
     * @var string
     */
    private $newValue;

    /**
     * @var integer
     */
    private $iteration;

    /**
     * @var boolean
     */
    private $flagForReview;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointPrimaryKey;

    /**
     * @var \Db\Entity\User
     */
    private $user;

    /**
     * @var \Db\Entity\Conversion
     */
    private $conversion;

    /**
     * @var \Db\Entity\ColumnDef
     */
    private $columnDef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataPointPrimaryKey = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set primaryKey
     *
     * @param string $primaryKey
     * @return DataPoint
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Get primaryKey
     *
     * @return string 
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set oldValue
     *
     * @param string $oldValue
     * @return DataPoint
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    /**
     * Get oldValue
     *
     * @return string 
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Set newValue
     *
     * @param string $newValue
     * @return DataPoint
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * Get newValue
     *
     * @return string 
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Set iteration
     *
     * @param integer $iteration
     * @return DataPoint
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
     * Set flagForReview
     *
     * @param boolean $flagForReview
     * @return DataPoint
     */
    public function setFlagForReview($flagForReview)
    {
        $this->flagForReview = $flagForReview;

        return $this;
    }

    /**
     * Get flagForReview
     *
     * @return boolean 
     */
    public function getFlagForReview()
    {
        return $this->flagForReview;
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
     * @return DataPoint
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
     * Set user
     *
     * @param \Db\Entity\User $user
     * @return DataPoint
     */
    public function setUser(\Db\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Db\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set conversion
     *
     * @param \Db\Entity\Conversion $conversion
     * @return DataPoint
     */
    public function setConversion(\Db\Entity\Conversion $conversion)
    {
        $this->conversion = $conversion;

        return $this;
    }

    /**
     * Get conversion
     *
     * @return \Db\Entity\Conversion 
     */
    public function getConversion()
    {
        return $this->conversion;
    }

    /**
     * Set columnDef
     *
     * @param \Db\Entity\ColumnDef $columnDef
     * @return DataPoint
     */
    public function setColumnDef(\Db\Entity\ColumnDef $columnDef)
    {
        $this->columnDef = $columnDef;

        return $this;
    }

    /**
     * Get columnDef
     *
     * @return \Db\Entity\ColumnDef 
     */
    public function getColumnDef()
    {
        return $this->columnDef;
    }
}
