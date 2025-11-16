<?php

namespace App\Entity\Traits;

use App\Diba\Helpers\OptionsHelper;
use App\Entity\Deleted\ClubsDeleted;
use App\Entity\Deleted\LotsDeleted;
use App\Entity\Libraries;
use App\Entity\Support\Genres;
use App\Entity\Support\Municipalities;
use App\Entity\Support\Warehouses;
use Doctrine\ORM\Mapping as ORM;

trait HistoricEntityTray
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_HISTORIC_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="lot_id", type="integer", nullable=true)
     */
    private $lotId;

    /**
     * @var int
     *
     * @ORM\Column(name="library_id", type="integer", nullable=true)
     */
    private $libraryId;

    /**
     * @var int
     *
     * @ORM\Column(name="club_id", type="integer", nullable=true)
     */
    private $clubId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="route", type="string", length=199, nullable=true)
     */
    public $route;

    /**
     * @var string|null
     *
     * @ORM\Column(name="incidence", type="string", length=255, nullable=true)
     */
    public $incidence;

    /**
     * @var int
     *
     * @ORM\Column(name="exceded", type="integer", nullable=false)
     */
    private $exceded = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="uses", type="integer", nullable=false)
     */
    private $uses = 0;


    /**
     * @var bool|null
     *
     * @ORM\Column(name="sended", type="boolean", nullable=false)
     */
    private $sended = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="send_id", type="string", length=255, nullable=true)
     */
    public $sendId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="preparet_at", type="sillydatetime", nullable=true)
     */
    private $preparetAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="received_in", type="sillydatetime", nullable=true)
     */
    private $receivedIn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="return_in", type="sillydatetime", nullable=true)
     */
    private $returnIn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="received_at", type="sillydatetime", nullable=true)
     */
    private $receivedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="returned_at", type="sillydatetime", nullable=true)
     */
    private $returnedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="picked_at", type="sillydatetime", nullable=true)
     */
    private $pickedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="transit_in", type="sillydatetime", nullable=true)
     */
    private $transitIn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="closed_at", type="sillydatetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var Authorship
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Authorship")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $authorship;

    /**
     * @var Municipalities
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Municipalities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="library_id", referencedColumnName="id")
     * })
     */
    private $municipality;

    /**
     * @var Warehouses
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Warehouses")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $warehouse;

    /**
     * @var Genres
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Genres")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id", nullable=true)
     */
    private $genre;

    /**
     * @var ClubsDeleted
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deleted\ClubsDeleted", inversedBy="historic")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="id")
     */
    private $club;

    /**
     * @var Libraries
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Libraries", inversedBy="historic")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id")
     */
    private $library;

    /**
     * @var LotsDeleted
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deleted\LotsDeleted", inversedBy="historic")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $lot;

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
     * Get the value of route
     *
     * @return  string|null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the value of route
     *
     * @param  string|null  $route
     *
     * @return  self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the value of incidence
     *
     * @return  string|null
     */
    public function getIncidence()
    {
        return $this->incidence;
    }

    /**
     * Set the value of incidence
     *
     * @param  string|null  $incidence
     *
     * @return  self
     */
    public function setIncidence($incidence)
    {
        $this->incidence = $incidence;

        return $this;
    }

    /**
     * Get the value of authorship
     *
     * @return  Authorship
     */
    public function getAuthorship()
    {
        return $this->authorship;
    }

    /**
     * Get the value of club
     *
     * @return  ClubsDeleted
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set the value of club
     *
     * @param  ClubsDeleted  $club
     *
     * @return  self
     */
    public function setClub(ClubsDeleted $club)
    {
        $this->club = $club;

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

        return $this;
    }

    /**
     * Get the value of lot
     *
     * @return  LotsDeleted
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set the value of lot
     *
     * @param  LotsDeleted  $lot
     *
     * @return  self
     */
    public function setLot(LotsDeleted $lot)
    {
        $this->lot = $lot;

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
     * Get the value of warehouse
     *
     * @return  Warehouses
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * Set the value of warehouses
     *
     * @param  Warehouses  $warehouse
     *
     * @return  self
     */
    public function setWarehouse(Warehouses $warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * Get the value of genre
     *
     * @return  Genres
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of genre
     *
     * @param  Genres  $genre
     *
     * @return  self
     */
    public function setGenre(Genres $genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get the value of lotId
     *
     * @return  int
     */
    public function getLotId()
    {
        return $this->lotId ?? $this->lot->getId() ?? null;
    }

    /**
     * Set the value of lotId
     *
     * @param  int  $lotId
     *
     * @return  self
     */
    public function setLotId($lotId)
    {
        $this->lotId = $lotId;

        return $this;
    }

    /**
     * Get the value of clubId
     *
     * @return  int
     */
    public function getClubId()
    {
        return $this->clubId ?? $this->club->getId() ?? null;
    }

    /**
     * Set the value of clubId
     *
     * @param  int  $clubId
     *
     * @return  self
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get the value of libraryId
     *
     * @return  int
     */
    public function getLibraryId()
    {
        return $this->libraryId;
    }

    /**
     * Set the value of libraryId
     *
     * @param  int  $libraryId
     *
     * @return  self
     */
    public function setLibraryId($libraryId)
    {
        $this->libraryId = $libraryId;

        return $this;
    }

    /**
     * Get the value of returnIn
     *
     * @return  \DateTime|null
     */
    public function getReturnIn()
    {
        return $this->returnIn;
    }

    /**
     * Set the value of returnIn
     *
     * @param  \DateTime|null  $returnIn
     *
     * @return  self
     */
    public function setReturnIn($returnIn)
    {
        $this->returnIn = $returnIn;

        return $this;
    }

    /**
     * Get the value of receivedAt
     *
     * @return  \DateTime|null
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * Set the value of receivedAt
     *
     * @param  \DateTime|null  $receivedAt
     *
     * @return  self
     */
    public function setReceivedAt($receivedAt)
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    /**
     * Get the value of returnedAt
     *
     * @return  \DateTime|null
     */
    public function getReturnedAt()
    {
        return $this->returnedAt;
    }

    /**
     * Set the value of returnedAt
     *
     * @param  \DateTime|null  $returnedAt
     *
     * @return  self
     */
    public function setReturnedAt($returnedAt)
    {
        $this->returnedAt = $returnedAt;

        return $this;
    }

    /**
     * Get the value of pickedAt
     *
     * @return  \DateTime|null
     */
    public function getPickedAt()
    {
        return $this->pickedAt;
    }

    /**
     * Set the value of pickedAt
     *
     * @param  \DateTime|null  $pickedAt
     *
     * @return  self
     */
    public function setPickedAt($pickedAt)
    {
        $this->pickedAt = $pickedAt;

        return $this;
    }

    /**
     * Get the value of transitIn
     *
     * @return  \DateTime|null
     */
    public function getTransitIn()
    {
        return $this->transitIn;
    }

    /**
     * Set the value of transitIn
     *
     * @param  \DateTime|null  $transitIn
     *
     * @return  self
     */
    public function setTransitIn($transitIn)
    {
        $this->transitIn = $transitIn;

        return $this;
    }

    /**
     * Get the value of closedAt
     *
     * @return  \DateTime|null
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * Set the value of closedAt
     *
     * @param  \DateTime|null  $closedAt
     *
     * @return  self
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * Get the value of preparetAt
     *
     * @return  \DateTime|null
     */
    public function getPreparetAt()
    {
        return $this->preparetAt;
    }

    /**
     * Set the value of preparetAt
     *
     * @param  \DateTime|null  $preparetAt
     *
     * @return  self
     */
    public function setPreparetAt($preparetAt)
    {
        $this->preparetAt = $preparetAt;

        return $this;
    }

    /**
     * Get the value of receivedIn
     *
     * @return  \DateTime|null
     */
    public function getReceivedIn()
    {
        return $this->receivedIn;
    }

    /**
     * Set the value of receivedIn
     *
     * @param  \DateTime|null  $receivedIn
     *
     * @return  self
     */
    public function setReceivedIn($receivedIn)
    {
        $this->receivedIn = $receivedIn;

        return $this;
    }

    /**
     * Get the value of sendId
     *
     * @return  string|null
     */
    public function getSendId()
    {
        return $this->sendId;
    }

    /**
     * Set the value of sendId
     *
     * @param  string|null  $sendId
     *
     * @return  self
     */
    public function setSendId($sendId)
    {
        $this->sendId = $sendId;

        return $this;
    }

    /**
     * Get the value of sended
     *
     * @return  bool|null
     */
    public function getSended()
    {
        return $this->sended;
    }

    /**
     * Set the value of sended
     *
     * @param  bool|null  $sended
     *
     * @return  self
     */
    public function setSended($sended)
    {
        $this->sended = $sended;

        return $this;
    }

    /**
     * Get the value of exceded
     *
     * @return  int
     */
    public function getExceded()
    {
        return $this->exceded;
    }

    /**
     * Set the value of exceded
     *
     * @param  int  $exceded
     *
     * @return  self
     */
    public function setExceded($exceded)
    {
        $this->exceded = $exceded;

        return $this;
    }

    /**
     * Get the value of uses
     *
     * @return  int
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Set the value of uses
     *
     * @param  int  $uses
     *
     * @return  self
     */
    public function setUses($uses)
    {
        $this->uses = $uses;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    public function makeReturnInDate($date = null)
    {
        if (method_exists($this->library, 'getType')) {
            $return_in = $date ?? new \DateTime('now');

            $this->returnIn = (mb_substr($this->getLot()->getSignature(), 0, 2) == 'LF') 
                ? $return_in->add(new \DateInterval('P' . OptionsHelper::get('max_return_library_lf') . 'D'))
                : $this->returnIn = ($this->library->getType() == 1)
                    ? $return_in->add(new \DateInterval('P' . OptionsHelper::get('max_return_library') . 'D'))
                    : $return_in->add(new \DateInterval('P' . OptionsHelper::get('max_return_bus') . 'D'))
            ;
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
        return method_exists($this->returnedAt, 'format') ? $this->returnedAt->format('d/m/Y') : '';
    }
}
