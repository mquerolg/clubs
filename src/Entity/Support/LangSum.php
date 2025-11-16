<?php

namespace App\Entity\Support;

use App\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * LangSum
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class LangSum extends Entity
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
     * @var int
     *
     * @ORM\Column(name="lang_sum", type="integer", nullable=false)
     */
    public $langSum = 0;

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
     * Get the value of langSum
     *
     * @return  int
     */
    public function getLangSum()
    {
        return $this->langSum;
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
        return $this->langSum;
    }
}
