<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableDef
 */
class TableDef
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
    private $primaryKey;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $columnDef;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $conversion;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->primaryKey = new \Doctrine\Common\Collections\ArrayCollection();
        $this->columnDef = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conversion = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TableDef
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
     * Add primaryKey
     *
     * @param \Db\Entity\PrimaryKeyDef $primaryKey
     * @return TableDef
     */
    public function addPrimaryKey(\Db\Entity\PrimaryKeyDef $primaryKey)
    {
        $this->primaryKey[] = $primaryKey;

        return $this;
    }

    /**
     * Remove primaryKey
     *
     * @param \Db\Entity\PrimaryKeyDef $primaryKey
     */
    public function removePrimaryKey(\Db\Entity\PrimaryKeyDef $primaryKey)
    {
        $this->primaryKey->removeElement($primaryKey);
    }

    /**
     * Get primaryKey
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Add columnDef
     *
     * @param \Db\Entity\ColumnDef $columnDef
     * @return TableDef
     */
    public function addColumnDef(\Db\Entity\ColumnDef $columnDef)
    {
        $this->columnDef[] = $columnDef;

        return $this;
    }

    /**
     * Remove columnDef
     *
     * @param \Db\Entity\ColumnDef $columnDef
     */
    public function removeColumnDef(\Db\Entity\ColumnDef $columnDef)
    {
        $this->columnDef->removeElement($columnDef);
    }

    /**
     * Get columnDef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getColumnDef()
    {
        return $this->columnDef;
    }

    /**
     * Add conversion
     *
     * @param \Db\Entity\Conversion $conversion
     * @return TableDef
     */
    public function addConversion(\Db\Entity\Conversion $conversion)
    {
        $this->conversion[] = $conversion;

        return $this;
    }

    /**
     * Remove conversion
     *
     * @param \Db\Entity\Conversion $conversion
     */
    public function removeConversion(\Db\Entity\Conversion $conversion)
    {
        $this->conversion->removeElement($conversion);
    }

    /**
     * Get conversion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConversion()
    {
        return $this->conversion;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $primaryKeyDef;


    /**
     * Add primaryKeyDef
     *
     * @param \Db\Entity\PrimaryKeyDef $primaryKeyDef
     * @return TableDef
     */
    public function addPrimaryKeyDef(\Db\Entity\PrimaryKeyDef $primaryKeyDef)
    {
        $this->primaryKeyDef[] = $primaryKeyDef;

        return $this;
    }

    /**
     * Remove primaryKeyDef
     *
     * @param \Db\Entity\PrimaryKeyDef $primaryKeyDef
     */
    public function removePrimaryKeyDef(\Db\Entity\PrimaryKeyDef $primaryKeyDef)
    {
        $this->primaryKeyDef->removeElement($primaryKeyDef);
    }

    /**
     * Get primaryKeyDef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrimaryKeyDef()
    {
        return $this->primaryKeyDef;
    }
    /**
     * @var string
     */
    private $url;


    /**
     * Set url
     *
     * @param string $url
     * @return TableDef
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }
}
