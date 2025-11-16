<?php

namespace App\Entity\Traits;

use App\Entity\Libraries;
use App\Entity\Support\LibraryMunicipality;
use App\Entity\Support\Municipalities;
use App\Entity\Support\Zones;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait ClubsEntityTray
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_CLUBS_SEQ", initialValue=1, allocationSize=1)
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    private $year;

    /**
     * @var int
     *
     * @ORM\Column(name="library_id", type="integer", nullable=true, updatable=false, insertable=false)
     */
    private $libraryId;

    /**
     * @var string|nullstatus
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="observations", type="string", length=255, nullable=true)
     */
    private $observations;

    /**
     * @var string|null
     *
     * @ORM\Column(name="typology", type="string", length=255, nullable=true)
     */
    private $typology;

    /**
     * @var bool
     *
     * @ORM\Column(name="external", type="boolean", nullable=false)
     */
    private $external = false;

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
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var Municipalities
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Municipalities")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", nullable=true)
     */
    private $municipality;

    /**
     * @var Zones
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Zones")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", nullable=true)
     */
    private $zone;

    /**
     * @var Libraries
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Libraries", inversedBy="clubs")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id")
     */
    private $library;

    /**
     * @var LibraryMunicipality
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\LibraryMunicipality")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id")
     */
    private $libraryMunicipality;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Historic", mappedBy="club")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $historic;

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
     * Get the value of year
     *
     * @return  string|null
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set the value of year
     *
     * @param  string|null  $year
     *
     * @return  self
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return  string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string|null  $description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Get the value of external
     *
     * @return  bool
     */
    public function getExternal()
    {
        return $this->external;
    }

    /**
     * Set the value of external
     *
     * @param  bool  $external
     *
     * @return  self
     */
    public function setExternal($external)
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Get the value of active
     *
     * @return  bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @param  bool  $active
     *
     * @return  self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of typology
     *
     * @return  string|null
     */
    public function getTypology()
    {
        return $this->typology;
    }

    /**
     * Set the value of typology
     *
     * @param  string|null  $typology
     *
     * @return  self
     */
    public function setTypology($typology)
    {
        $this->typology = $typology;

        return $this;
    }

    /**
     * Get the value of libraryId
     *
     * @return  int|null
     */
    public function getLibraryId()
    {
        return $this->libraryId ?? $this->library->getId() ?? null;
    }

    /**
     * Set the value of libraryId
     *
     * @param  int|null  $libraryId
     *
     * @return  self
     */
    public function setLibraryId($libraryId)
    {
        $this->libraryId = $libraryId;

        return $this;
    }

    /**
     * Get the value of library
     *
     * @return  Libraries
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * Set the value of library
     *
     * @param  Libraries  $library
     *
     * @return  self
     */
    public function setLibrary(Libraries $library)
    {
        $this->library = $library;
        $this->libraryId = $library->getId();

        return $this;
    }

    /**
     * Get the value of municipality
     *
     * @return  Municipalities
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set the value of municipality
     *
     * @param  Municipalities  $municipality
     *
     * @return  self
     */
    public function setMunicipality(Municipalities $municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get the value of zone
     *
     * @return  Zones
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set the value of zone
     *
     * @param  Zones  $zone
     *
     * @return  self
     */
    public function setZone(Zones $zone)
    {
        $this->zone = $zone;

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
     * Get the value of libraryMunicipality
     *
     * @return  LibraryMunicipality
     */
    public function getLibraryMunicipality()
    {
        return $this->libraryMunicipality;
    }

    /**
     * Set the value of libraryMunicipality
     *
     * @param  LibraryMunicipality  $libraryMunicipality
     *
     * @return  self
     */
    public function setLibraryMunicipality(LibraryMunicipality $libraryMunicipality)
    {
        $this->libraryMunicipality = $libraryMunicipality;

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
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString(): string
    {
        return $this->name ?? '--';
    }
}
