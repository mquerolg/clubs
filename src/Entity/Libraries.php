<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Libraries
 *
 * @ORM\Table(name="CLUBS_LIBRARIES")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Libraries extends Entity
{
    use SoftDeleteableEntity;

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

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Clubs", mappedBy="library")
     */
    private $clubs;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Historic", mappedBy="library")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $historic;

    /**
     * @var Localizations
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Localizations")
     * @ORM\JoinColumn(name="code", referencedColumnName="loc_iii_id")
     */
    private $localization;

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

    /**
     * Get the value of clubs
     *
     * @return  ArrayCollection
     */
    public function getClubs()
    {
        return $this->clubs;
    }

    /**
     * Set the value of clubs
     *
     * @param  ArrayCollection  $clubs
     *
     * @return  self
     */
    public function setClubs(ArrayCollection $clubs)
    {
        $this->clubs = $clubs;

        return $this;
    }

    /**
     * Get the value of historic
     *
     * @return  ArrayCollection
     */
    public function getHistoric()
    {
        return $this->historic;
    }

    /**
     * Set the value of historic
     *
     * @param  ArrayCollection  $historic
     *
     * @return  self
     */
    public function setHistoric(ArrayCollection $historic)
    {
        $this->historic = $historic;

        return $this;
    }

    /**
     * Get the value of localization
     *
     * @return  Localizations|null
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * Set the value of localization
     *
     * @param  Localizations  $localization
     *
     * @return  self
     */
    public function setLocalization(Localizations $localization)
    {
        $this->localization = $localization;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Increment the value of use_lots and total_lots
     *
     * @return  self
     */
    public function addLot()
    {
        if (is_countable($this->historic)) {
            $this->totalLots = $this->historic->count() + 1;

            $this->useLots = $this->historic->filter(function ($entry) {
                return is_null($entry->getClosedAt());
            })->count() + 1;
        } else {
            $this->totalLots++;
            $this->useLots++;
        }

        return $this;
    }

    /**
     * Decrement the value of use_lots
     *
     * @return  self
     */
    public function substractLot()
    {
        if (is_countable($this->historic)) {
            $this->useLots = $this->historic->filter(function ($entry) {
                return is_null($entry->getClosedAt());
            })->count() - 1;
        } else {
            $this->useLots--;
        }

        if ($this->useLots < 0) {
            $this->useLots = 0;
        }

        return $this;
    }

    /**
     * Increment the value of total_clubs
     *
     * @return  self
     */
    public function addClub()
    {
        if (is_countable($this->clubs)) {
            $this->totalClubs = $this->clubs->count() + 1;
        } else {
            $this->totalClubs++;
        }

        return $this;
    }

    /**
     * Decrement the value of totalClubs
     *
     * @return  self
     */
    public function substractClub()
    {
        if (is_countable($this->clubs)) {
            $this->totalClubs = $this->clubs->count() - 1;
        } else {
            $this->totalClubs--;
        }

        if ($this->totalClubs < 0) {
            $this->totalClubs = 0;
        }

        return $this;
    }

    /**
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString(): string
    {
        return $this->name ?? '--';
    }
}
