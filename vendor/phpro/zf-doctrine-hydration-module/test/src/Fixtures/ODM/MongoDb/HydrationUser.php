<?php

namespace PhproTest\DoctrineHydrationModule\Fixtures\ODM\MongoDb;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class HydrationUser
 *
 * @package PhproTest\DoctrineHydrationModule\Tests\Fixtures\ODM\MongoDb
 *
 * @ODM\Document
 */
class HydrationUser
{
    /** @ODM\Id */
    public $id;

    /** @ODM\String */
    public $name;

    /**
     * @ODM\Date
     * @var \DateTime
     */
    public $birthday;

    /**
     * @ODM\Timestamp
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @ODM\ReferenceOne(targetDocument="HydrationReferenceOne")
     */
    public $referenceOne;

    /**
     * @ODM\ReferenceMany(targetDocument="HydrationReferenceMany")
     * @var ArrayCollection
     */
    public $referenceMany = array();

    /**
     * @ODM\EmbedOne(targetDocument="HydrationEmbedOne")
     */
    public $embedOne;

    /**
     * @ODM\EmbedMany(targetDocument="HydrationEmbedMany")
     * @var ArrayCollection
     */
    public $embedMany;

    /**
     * Basic state
     */
    public function __construct()
    {
        $this->embedMany = new ArrayCollection();
        $this->referenceMany = new ArrayCollection();

        $now = new \DateTime();
        $this->createdAt = $now->getTimestamp();
    }

    /**
     * @param mixed $embedOne
     */
    public function setEmbedOne($embedOne)
    {
        $this->embedOne = $embedOne;
    }

    /**
     * @return mixed
     */
    public function getEmbedOne()
    {
        return $this->embedOne;
    }

    /**
     * @param mixed $embedMany
     */
    public function setEmbedMany($embedMany)
    {
        $this->embedMany = $embedMany;
    }

    /**
     * @return mixed
     */
    public function getEmbedMany()
    {
        return $this->embedMany;
    }

    /**
     * @param $embedMany
     */
    public function addEmbedMany($embedMany)
    {
        foreach ($embedMany as $record) {
            $this->embedMany->add($record);
        }
    }

    /**
     * @param $embedMany
     */
    public function removeEmbedMany($embedMany)
    {
        foreach ($embedMany as $record) {
            $this->embedMany->removeElement($record);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $referenceMany
     */
    public function setReferenceMany($referenceMany)
    {
        $this->referenceMany = $referenceMany;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReferenceMany()
    {
        return $this->referenceMany;
    }

    /**
     * @param $referenceMany
     */
    public function addReferenceMany($referenceMany)
    {
        foreach ($referenceMany as $record) {
            $this->referenceMany->add($record);
        }
    }

    /**
     * @param $referenceMany
     */
    public function removeReferenceMany($referenceMany)
    {
        foreach ($referenceMany as $record) {
            $this->referenceMany->removeElement($record);
        }
    }

    /**
     * @param mixed $referenceOne
     */
    public function setReferenceOne($referenceOne)
    {
        $this->referenceOne = $referenceOne;
    }

    /**
     * @return mixed
     */
    public function getReferenceOne()
    {
        return $this->referenceOne;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
