<?php

namespace App\Entity\Support;

use Doctrine\ORM\Mapping as ORM;

/**
 * LibraryMunicipality : Virtual class, acts as an extension of Libraries
 *
 * @ORM\Table(name="CLUBS_LIBRARIES")
 * @ORM\Entity
 */
class LibraryMunicipality
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_LIBRARIES_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    public $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="municipality", type="string", length=255, nullable=true)
     */
    private $municipality;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="total_clubs", type="integer", nullable=false)
     */
    private $totalClubs = 0;

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of municipality
     *
     * @return  string|null
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Get the value of active
     *
     * @return  bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @param  bool|null  $active
     *
     * @return  self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return  string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string|null  $name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of totalClubs
     *
     * @return  int
     */
    public function getTotalClubs()
    {
        return $this->totalClubs;
    }

    /**
     * Set the value of totalClubs
     *
     * @param  int  $totalClubs
     *
     * @return  self
     */
    public function setTotalClubs(int $totalClubs)
    {
        $this->totalClubs = $totalClubs;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Increment the value of total_clubs
     *
     * @return  self
     */
    public function addClub()
    {
        $this->totalClubs++;

        return $this;
    }

    /**
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString(): string
    {
        return $this->municipality . ' - ' . $this->name;
    }
}
