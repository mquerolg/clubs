<?php

namespace App\Entity\Support;

use App\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Authorship
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class Authorship extends Entity
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
     * @var string|null
     *
     * @ORM\Column(name="authorship", type="string", length=255, nullable=true)
     */
    public $authorship;

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
     * Get the value of authorship
     *
     * @return  string|null
     */
    public function getAuthorship()
    {
        return $this->authorship;
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
        return $this->authorship ?? '--';
    }
}
