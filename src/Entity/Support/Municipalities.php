<?php

namespace App\Entity\Support;

use Doctrine\ORM\Mapping as ORM;

/**
 * Municipalities : Virtual class, acts as an extension of Libraries
 *
 * @ORM\Table(name="CLUBS_LIBRARIES")
 * @ORM\Entity
 */
class Municipalities
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
     * @ORM\Column(name="municipality", type="string", length=255, nullable=true)
     */
    private $municipality;

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
        return $this->municipality ?? '--';
    }
}
