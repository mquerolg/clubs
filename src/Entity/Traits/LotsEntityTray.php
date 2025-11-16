<?php

namespace App\Entity\Traits;

use App\Entity\Genres;
use App\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait LotsEntityTray
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_LOTS_SEQ", initialValue=1, allocationSize=1)
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="exemplar", type="string", nullable=true)
     */
    private $exemplar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authorship", type="string", length=255, nullable=true)
     */
    public $authorship;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    public $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="signature", type="string", length=255, nullable=true)
     */
    public $signature;

    /**
     * @var string|null
     *
     * @ORM\Column(name="warehouse", type="string", length=255, nullable=true)
     */
    private $warehouse;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bibliographic", type="string", length=255, nullable=true)
     */
    private $bibliographic;

    /**
     * @var string|null
     *
     * @ORM\Column(name="owner", type="string", length=255, nullable=true)
     */
    private $owner;

    /**
     * @var int|null
     *
     * @ORM\Column(name="uses", type="integer", nullable=true)
     */
    private $uses = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="classification", type="string", length=255, nullable=true)
     */
    private $classification;

    /**
     * @var string|null
     *
     * @ORM\Column(name="collection", type="string", length=255, nullable=true)
     */
    private $collection;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    public $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="isbn", type="string", length=255, nullable=true)
     */
    private $isbn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="observations", type="string", length=2048, nullable=true)
     */
    private $observations;

    /**
     * @var int|null
     *
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    private $year;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_cat", type="integer", nullable=false)
     */
    public $langCat = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_es", type="integer", nullable=false)
     */
    public $langEs = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_ang", type="integer", nullable=false)
     */
    private $langAng = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_fra", type="integer", nullable=false)
     */
    private $langFra = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_ita", type="integer", nullable=false)
     */
    private $langIta = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_ale", type="integer", nullable=false)
     */
    private $langAle = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_others", type="integer", nullable=false)
     */
    public $langOthers = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="lang_sum", type="integer", nullable=false)
     */
    public $langSum = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="publication", type="string", length=255, nullable=true)
     */
    private $publication;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dim", type="string", length=255, nullable=true)
     */
    private $dim;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="reserved", type="boolean", nullable=false)
     */
    private $reserved = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="genre_id", type="integer", nullable=true)
     */
    private $genreId;

    /**
     * @var Genres
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Genres")
     * @ORM\JoinColumn(name="genre_id", referencedColumnName="id")
     */
    private $genre;

    /**
     * @var Status
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Copies", mappedBy="lot")
     */
    private $copies;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Historic", mappedBy="lot")
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
     * Get the value of authorship
     *
     * @return  string|null
     */
    public function getAuthorship()
    {
        return $this->authorship;
    }

    /**
     * Set the value of authorship
     *
     * @param  string|null  $authorship
     *
     * @return  self
     */
    public function setAuthorship($authorship)
    {
        $this->authorship = $authorship;

        return $this;
    }

    /**
     * Get the value of title
     *
     * @return  string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param  string|null  $title
     *
     * @return  self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of signature
     *
     * @return  string|null
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set the value of signature
     *
     * @param  string|null  $signature
     *
     * @return  self
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get the value of warehouse
     *
     * @return  string|null
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * Set the value of warehouse
     *
     * @param  string|null  $warehouse
     *
     * @return  self
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * Get the value of bibliographic
     *
     * @return  string|null
     */
    public function getBibliographic()
    {
        return $this->bibliographic;
    }

    /**
     * Set the value of bibliographic
     *
     * @param  string|null  $bibliographic
     *
     * @return  self
     */
    public function setBibliographic($bibliographic)
    {
        $this->bibliographic = $bibliographic;

        return $this;
    }

    /**
     * Get the value of owner
     *
     * @return  string|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set the value of owner
     *
     * @param  string|null  $owner
     *
     * @return  self
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get the value of uses
     *
     * @return  int|null
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Set the value of uses
     *
     * @param  int|null  $uses
     *
     * @return  self
     */
    public function setUses($uses)
    {
        $this->uses = $uses;

        return $this;
    }

    /**
     * Get the value of classification
     *
     * @return  string|null
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * Set the value of classification
     *
     * @param  string|null  $classification
     *
     * @return  self
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;

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
     * Get the value of isbn
     *
     * @return  string|null
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set the value of isbn
     *
     * @param  string|null  $isbn
     *
     * @return  self
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;

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
     * Get the value of year
     *
     * @return  int|null
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set the value of year
     *
     * @param  int|null  $year
     *
     * @return  self
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the value of publication
     *
     * @return  string|null
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Set the value of publication
     *
     * @param  string|null  $publication
     *
     * @return  self
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get the value of dim
     *
     * @return  string|null
     */
    public function getDim()
    {
        return $this->dim;
    }

    /**
     * Set the value of dim
     *
     * @param  string|null  $dim
     *
     * @return  self
     */
    public function setDim($dim)
    {
        $this->dim = $dim;

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
     * Get the value of reserved
     *
     * @return  bool|null
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * Set the value of reserved
     *
     * @param  bool|null  $reserved
     *
     * @return  self
     */
    public function setReserved($reserved)
    {
        $this->reserved = $reserved;

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
     * Get the value of status
     *
     * @return  Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param  Status  $status
     *
     * @return  self
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of langCat
     *
     * @return  int|null
     */
    public function getLangCat()
    {
        return $this->langCat;
    }

    /**
     * Set the value of langCat
     *
     * @param  int|null  $langCat
     *
     * @return  self
     */
    public function setLangCat($langCat)
    {
        $this->langCat = $langCat;

        $this->setLangSum();

        return $this;
    }

    /**
     * Get the value of langEs
     *
     * @return  int|null
     */
    public function getLangEs()
    {
        return $this->langEs;
    }

    /**
     * Set the value of langEs
     *
     * @param  int|null  $langEs
     *
     * @return  self
     */
    public function setLangEs($langEs)
    {
        $this->langEs = $langEs;

        $this->setLangSum();

        return $this;
    }

    /**
     * Get the value of langAng
     *
     * @return  int|null
     */
    public function getLangAng()
    {
        return $this->langAng;
    }

    /**
     * Set the value of langAng
     *
     * @param  int|null  $langAng
     *
     * @return  self
     */
    public function setLangAng($langAng)
    {
        $this->langAng = $langAng;

        $this->setLangSum();
        $this->setLangOthers();

        return $this;
    }

    /**
     * Get the value of langFra
     *
     * @return  int|null
     */
    public function getLangFra()
    {
        return $this->langFra;
    }

    /**
     * Set the value of langFra
     *
     * @param  int|null  $langFra
     *
     * @return  self
     */
    public function setLangFra($langFra)
    {
        $this->langFra = $langFra;

        $this->setLangSum();
        $this->setLangOthers();

        return $this;
    }

    /**
     * Get the value of langIta
     *
     * @return  int|null
     */
    public function getLangIta()
    {
        return $this->langIta;
    }

    /**
     * Set the value of langIta
     *
     * @param  int|null  $langIta
     *
     * @return  self
     */
    public function setLangIta($langIta)
    {
        $this->langIta = $langIta;

        $this->setLangSum();
        $this->setLangOthers();

        return $this;
    }

    /**
     * Get the value of langAle
     *
     * @return  int|null
     */
    public function getLangAle()
    {
        return $this->langAle;
    }

    /**
     * Set the value of langAle
     *
     * @param  int|null  $langAle
     *
     * @return  self
     */
    public function setLangAle($langAle)
    {
        $this->langAle = $langAle;

        $this->setLangSum();
        $this->setLangOthers();

        return $this;
    }

    /**
     * Get the value of langOthers
     *
     * @return  int
     */
    public function getLangOthers()
    {
        return $this->langOthers;
    }

    /**
     * Set the value of langOthers
     *
     * @return  self
     */
    public function setLangOthers()
    {
        $this->langOthers = $this->langAng + $this->langFra + $this->langIta + $this->langAle;

        return $this;
    }

    /**
     * Get the value of langSum
     *
     * @return  int
     */
    public function getLangSum()
    {
        return $this->langSum;
    }

    /**
     * Set the value of langSum
     *
     * @return  self
     */
    public function setLangSum()
    {
        $this->langSum = $this->langCat + $this->langEs + $this->langAng + $this->langFra + $this->langIta + $this->langAle;

        return $this;
    }

    /**
     * Get the value of genreId
     *
     * @return  int|null
     */
    public function getGenreId()
    {
        return $this->genreId;
    }

    /**
     * Set the value of genreId
     *
     * @param  int|null  $genreId
     *
     * @return  self
     */
    public function setGenreId($genreId)
    {
        $this->genreId = $genreId;

        return $this;
    }

    /**
     * Get the value of statusId
     *
     * @return  int|null
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * Set the value of statusId
     *
     * @param  int|null  $statusId
     *
     * @return  self
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;

        return $this;
    }

    /**
     * Get the value of collection
     *
     * @return  string|null
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set the value of collection
     *
     * @param  string|null  $collection
     *
     * @return  self
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get the value of url
     *
     * @return  string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @param  string|null  $url
     *
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of copies
     *
     * @return  ArrayCollection
     */
    public function getCopies()
    {
        return $this->copies;
    }

    /**
     * Set the value of copies
     *
     * @param  ArrayCollection  $copies
     *
     * @return  self
     */
    public function setCopies(ArrayCollection $copies)
    {
        $this->copies = $copies;

        return $this;
    }

    /**
     * Get the value of exemplar
     *
     * @return  string
     */
    public function getExemplar()
    {
        return $this->exemplar;
    }

    /**
     * Set the value of exemplar
     *
     * @param  string  $exemplar
     *
     * @return  self
     */
    public function setExemplar(string $exemplar)
    {
        $this->exemplar = $exemplar;

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

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString(): string
    {
        return $this->title ?? '--';
    }
}
