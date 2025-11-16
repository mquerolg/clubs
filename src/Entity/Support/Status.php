<?php

namespace App\Entity\Support;

use App\Entity\Entity;
use App\Entity\Status as State;
use Doctrine\ORM\Mapping as ORM;

/**
 * Status
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class Status extends Entity
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
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

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
     * Get the value of status
     *
     * @return  State
     */
    public function getStatus()
    {
        return $this->status;
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
        return $this->getStatus()->getName() ?? '--';
    }
}
