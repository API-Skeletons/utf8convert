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
     * @var integer
     */
    private $id;

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
        $this->dataPointPrimaryKeyDef = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'primaryKey' => $this->getPrimaryKey(),
            'oldValue' => $this->getOldValue(),
            'newValue' => $this->getNewValue(),
            'comment' => $this->getComment(),
            'flagged' => $this->getFlagged(),
            'approved' => $this->getApproved(),
            'denied' => $this->getDenied(),
            'getImportedAt' => $this->getImportedAt(),
        );
    }

    public function exchangeArray()
    {

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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $convertWorker;


    /**
     * Add convertWorker
     *
     * @param \Db\Entity\ConvertWorker $convertWorker
     * @return DataPoint
     */
    public function addConvertWorker(\Db\Entity\ConvertWorker $convertWorker)
    {
        $this->convertWorker[] = $convertWorker;

        return $this;
    }

    /**
     * Remove convertWorker
     *
     * @param \Db\Entity\ConvertWorker $convertWorker
     */
    public function removeConvertWorker(\Db\Entity\ConvertWorker $convertWorker)
    {
        $this->convertWorker->removeElement($convertWorker);
    }

    /**
     * Get convertWorker
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConvertWorker()
    {
        return $this->convertWorker;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointIteration;


    /**
     * Add dataPointIteration
     *
     * @param \Db\Entity\DataPointIteration $dataPointIteration
     * @return DataPoint
     */
    public function addDataPointIteration(\Db\Entity\DataPointIteration $dataPointIteration)
    {
        $this->dataPointIteration[] = $dataPointIteration;

        return $this;
    }

    /**
     * Remove dataPointIteration
     *
     * @param \Db\Entity\DataPointIteration $dataPointIteration
     */
    public function removeDataPointIteration(\Db\Entity\DataPointIteration $dataPointIteration)
    {
        $this->dataPointIteration->removeElement($dataPointIteration);
    }

    /**
     * Get dataPointIteration
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDataPointIteration()
    {
        return $this->dataPointIteration;
    }
    /**
     * @var boolean
     */
    private $flagged;

    /**
     * @var boolean
     */
    private $approved;


    /**
     * Set flagged
     *
     * @param boolean $flagged
     * @return DataPoint
     */
    public function setFlagged($flagged)
    {
        $this->flagged = $flagged;

        return $this;
    }

    /**
     * Get flagged
     *
     * @return boolean
     */
    public function getFlagged()
    {
        return $this->flagged;
    }

    /**
     * Set approved
     *
     * @param boolean $approved
     * @return DataPoint
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean
     */
    public function getApproved()
    {
        return $this->approved;
    }
    /**
     * @var string
     */
    private $comment;


    /**
     * Set comment
     *
     * @param string $comment
     * @return DataPoint
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    /**
     * @var boolean
     */
    private $denied;

    /**
     * @var \DateTime
     */
    private $importedAt;


    /**
     * Set denied
     *
     * @param boolean $denied
     * @return DataPoint
     */
    public function setDenied($denied)
    {
        $this->denied = $denied;

        return $this;
    }

    /**
     * Get denied
     *
     * @return boolean
     */
    public function getDenied()
    {
        return $this->denied;
    }

    /**
     * Set importedAt
     *
     * @param \DateTime $importedAt
     * @return DataPoint
     */
    public function setImportedAt($importedAt)
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    /**
     * Get importedAt
     *
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }
    /**
     * @var \DateTime
     */
    private $convertedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dataPointPrimaryKeyDef;


    /**
     * Set convertedAt
     *
     * @param \DateTime $convertedAt
     *
     * @return DataPoint
     */
    public function setConvertedAt($convertedAt)
    {
        $this->convertedAt = $convertedAt;

        return $this;
    }

    /**
     * Get convertedAt
     *
     * @return \DateTime
     */
    public function getConvertedAt()
    {
        return $this->convertedAt;
    }

    /**
     * Add dataPointPrimaryKeyDef
     *
     * @param \Db\Entity\DataPointPrimaryKeyDef $dataPointPrimaryKeyDef
     *
     * @return DataPoint
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
    /**
     * @var bool|null
     */
    private $errored;


    /**
     * Set errored.
     *
     * @param bool|null $errored
     *
     * @return DataPoint
     */
    public function setErrored($errored = null)
    {
        $this->errored = $errored;

        return $this;
    }

    /**
     * Get errored.
     *
     * @return bool|null
     */
    public function getErrored()
    {
        return $this->errored;
    }
}
