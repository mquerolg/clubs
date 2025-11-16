<?php

namespace App\Entity\Reports;

use Doctrine\ORM\Mapping as ORM;

/**
 * MunicipalityReport
 *
 * @ORM\Table(name="CLUBS_REPORT_MUNICIPALITY")
 * @ORM\Entity
 */
class MunicipalityReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_REPORT_MUNICIPALITY_SEQ", initialValue=1, allocationSize=1)
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
     * @ORM\Column(name="municipality", type="string", nullable=false)
     */
    private $municipality;

    /**
     * @var int
     *
     * @ORM\Column(name="clubs", type="integer", nullable=false)
     */
    private $clubs = 0;

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
     * Get the value of clubs
     *
     * @return  int
     */
    public function getClubs()
    {
        return $this->clubs;
    }

    /**
     * Set the value of clubs
     *
     * @param  int  $clubs
     *
     * @return  self
     */
    public function setClubs(int $clubs)
    {
        $this->clubs = $clubs;

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
}
