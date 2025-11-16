<?php

namespace App\Entity\Support;

use App\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * UpdateLibraries
 *
 * @ORM\Table(name="CLUBS_LIBRARIES")
 * @ORM\Entity
 */
class UpdateLibraries extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, updatable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_LIBRARIES_SEQ", initialValue=1, allocationSize=1)
     */
    public $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_diba", type="string", length=255, nullable=false)
     */
    private $idDiba;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active = false;

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
    public $municipality;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zone", type="string", length=255, nullable=true)
     */
    private $zone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="observations", type="text", length=65535, nullable=true)
     */
    private $observations;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="use_lots", type="integer", nullable=false)
     */
    private $useLots = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="total_lots", type="integer", nullable=false)
     */
    private $totalLots = 0;

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
     * Set the value of id
     *
     * @param  int  $id
     *
     * @return  self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
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
     * Get the value of municipality
     *
     * @return  string|null
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set the value of municipality
     *
     * @param  string|null  $municipality
     *
     * @return  self
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return  string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  string|null  $email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of code
     *
     * @return  string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @param  string $code
     *
     * @return  self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of zone
     *
     * @return  string|null
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set the value of zone
     *
     * @param  string|null  $zone
     *
     * @return  self
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get the value of observations
     *
     * @return  string|null
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * Set the value of observations
     *
     * @param  string|null  $observations
     *
     * @return  self
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    /**
     * Get the value of idDiba
     *
     * @return  string|null
     */
    public function getIdDiba()
    {
        return $this->idDiba;
    }

    /**
     * Set the value of idDiba
     *
     * @param  string|null  $idDiba
     *
     * @return  self
     */
    public function setIdDiba($idDiba)
    {
        $this->idDiba = $idDiba;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param  int  $type
     *
     * @return  self
     */
    public function setType(int $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of useLots
     *
     * @return  int
     */
    public function getUseLots()
    {
        return $this->useLots;
    }

    /**
     * Set the value of useLots
     *
     * @param  int  $useLots
     *
     * @return  self
     */
    public function setUseLots(int $useLots)
    {
        $this->useLots = $useLots;

        return $this;
    }

    /**
     * Get the value of totalLots
     *
     * @return  int
     */
    public function getTotalLots()
    {
        return $this->totalLots;
    }

    /**
     * Set the value of totalLots
     *
     * @param  int  $totalLots
     *
     * @return  self
     */
    public function setTotalLots(int $totalLots)
    {
        $this->totalLots = $totalLots;

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
}
