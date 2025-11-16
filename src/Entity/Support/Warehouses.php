<?php

namespace App\Entity\Support;

use Doctrine\ORM\Mapping as ORM;

/**
 * Warehouses : Virtual class, acts as an extension of Lots
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class Warehouses
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_LOTS_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="warehouse", type="string", length=255, nullable=false)
     */
    private $warehouse;

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
     * Get the value of warehouse
     *
     * @return  string|null
     */
    public function getWarehouse()
    {
        return $this->warehouse;
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
        return $this->warehouse ?? '--';
    }
}
