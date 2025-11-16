<?php

namespace App\Entity\Support;

use App\Entity\Entity;
use App\Entity\Genres as Genre;
use Doctrine\ORM\Mapping as ORM;

/**
 * Genres
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class Genres extends Entity
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
     * @var Genre
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Genres")
     * @ORM\JoinColumn(name="genre_id", referencedColumnName="id")
     */
    private $genre;

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
     * Get the value of genre
     *
     * @return  Genre
     */
    public function getGenre()
    {
        return $this->genre;
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
        return $this->getGenre()->getName() ?? '--';
    }
}
