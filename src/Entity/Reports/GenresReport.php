<?php

namespace App\Entity\Reports;

use Doctrine\ORM\Mapping as ORM;

/**
 * GenresReport
 *
 * @ORM\Table(name="CLUBS_REPORT_GENRES")
 * @ORM\Entity
 */
class GenresReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_REPORT_GENRES_SEQ", initialValue=1, allocationSize=1)
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
     * @ORM\Column(name="genre", type="string", nullable=false)
     */
    private $genre;

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
     * @var int
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total = 0;

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
     * Get the value of genre
     *
     * @return  string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of genre
     *
     * @param  string  $genre
     *
     * @return  self
     */
    public function setGenre(string $genre)
    {
        $this->genre = $genre;

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
     * Get the value of total
     *
     * @return  int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set the value of total
     *
     * @param  int  $total
     *
     * @return  self
     */
    public function setTotal(int $total)
    {
        $this->total = $total;

        return $this;
    }
}
