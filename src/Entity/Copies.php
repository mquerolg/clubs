<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Copies
 *
 * @ORM\Table(name="CLUBS_COPIES")
 * @ORM\Entity
 */
class Copies
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_COPIES_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var Lots
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Lots", inversedBy="copies")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $lot;

    /**
     * @var int
     *
     * @ORM\Column(name="lot_id", type="integer", nullable=false)
     */
    private $lotId;

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
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of lot
     *
     * @return  Lots
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set the value of lot
     *
     * @param  Lots  $lot
     *
     * @return  self
     */
    public function setLot(Lots $lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get the value of lotId
     *
     * @return  int
     */
    public function getLotId()
    {
        return $this->lotId;
    }

    /**
     * Set the value of lotId
     *
     * @param  int  $lotId
     *
     * @return  self
     */
    public function setLotId(int $lotId)
    {
        $this->lotId = $lotId;

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
        return $this->name ?? '--';
    }
}
