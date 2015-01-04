<?php

namespace Db\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Table
 */
class Table
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
    private $column;

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
        $this->column = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conversion = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Table
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
     * @param \Db\Entity\PrimaryKey $primaryKey
     * @return Table
     */
    public function addPrimaryKey(\Db\Entity\PrimaryKey $primaryKey)
    {
        $this->primaryKey[] = $primaryKey;

        return $this;
    }

    /**
     * Remove primaryKey
     *
     * @param \Db\Entity\PrimaryKey $primaryKey
     */
    public function removePrimaryKey(\Db\Entity\PrimaryKey $primaryKey)
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
     * Add column
     *
     * @param \Db\Entity\Column $column
     * @return Table
     */
    public function addColumn(\Db\Entity\Column $column)
    {
        $this->column[] = $column;

        return $this;
    }

    /**
     * Remove column
     *
     * @param \Db\Entity\Column $column
     */
    public function removeColumn(\Db\Entity\Column $column)
    {
        $this->column->removeElement($column);
    }

    /**
     * Get column
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Add conversion
     *
     * @param \Db\Entity\Conversion $conversion
     * @return Table
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
}
