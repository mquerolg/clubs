<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoricRequest : Virtual class, acts as an extension of Historic
 *
 * @ORM\Table(name="CLUBS_HISTORIC")
 * @ORM\Entity
 */
class HistoricRequest extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_HISTORIC_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString(): string
    {
        return $this->createdAt->format('d/m/Y') ?? '';
    }
}
