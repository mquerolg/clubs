<?php

namespace App\Entity\Reports;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClubsLotsReport
 *
 * @ORM\Table(name="CLUBS_REPORT_CLUBS_LOTS")
 * @ORM\Entity
 */
class ClubsLotsReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_REPORT_CLUBS_LOTS_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="integer", nullable=false)
     * @ORM\OrderBy({"year" = "DESC"})
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="club", type="string", nullable=false)
     */
    private $club;

    /**
     * @var string
     *
     * @ORM\Column(name="library", type="string", nullable=false)
     */
    private $library;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality", type="string", nullable=false)
     */
    private $municipality;

    /**
     * @var string
     *
     * @ORM\Column(name="zone", type="string", nullable=false)
     */
    private $zone;

    /**
     * @var int
     *
     * @ORM\Column(name="lots", type="integer", nullable=false)
     */
    private $lots = 0;

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
     * Get the value of year
     *
     * @return  int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set the value of year
     *
     * @param  int  $year
     *
     * @return  self
     */
    public function setYear(int $year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the value of zone
     *
     * @return  string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set the value of zone
     *
     * @param  string  $zone
     *
     * @return  self
     */
    public function setZone(string $zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get the value of lots
     *
     * @return  int
     */
    public function getLots()
    {
        return $this->lots;
    }

    /**
     * Set the value of lots
     *
     * @param  int  $lots
     *
     * @return  self
     */
    public function setLots(int $lots)
    {
        $this->lots = $lots;

        return $this;
    }

    /**
     * Get the value of municipality
     *
     * @return  string
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set the value of municipality
     *
     * @param  string  $municipality
     *
     * @return  self
     */
    public function setMunicipality(string $municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get the value of library
     *
     * @return  string
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * Set the value of library
     *
     * @param  string  $library
     *
     * @return  self
     */
    public function setLibrary(string $library)
    {
        $this->library = $library;

        return $this;
    }

    /**
     * Get the value of club
     *
     * @return  string
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set the value of club
     *
     * @param  string  $club
     *
     * @return  self
     */
    public function setClub(string $club)
    {
        $this->club = $club;

        return $this;
    }
}
