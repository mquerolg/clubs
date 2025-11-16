<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 */
class Entity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="sillydatetime", nullable=false)
     */
    public $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="sillydatetime", nullable=false)
     */
    public $createdAt;

    /*
    |--------------------------------------------------------------------------
    | GONSTRUCT
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->updatedAt = $this->updatedAt ?? new \DateTime('now');
        $this->createdAt = $this->createdAt ?? new \DateTime('now');
    }

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of updatedAt
     *
     * @return  \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt ?? new \DateTime('now');
    }

    /**
     * Set the value of updatedAt
     *
     * @param  \DateTime|null  $updatedAt
     *
     * @return  self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt ?? new \DateTime('now');

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt ?? new \DateTime('now');
    }

    /**
     * Set the value of createdAt
     *
     * @param  \DateTime|null  $createdAt
     *
     * @return  self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt ?? new \DateTime('now');

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    public function getUpdatedFormat()
    {
        return $this->setUpdatedFormat($this->getUpdatedAt());
    }

    public function setUpdatedFormat(\DateTime $date)
    {
        $dias = ['diumenge', 'dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres', 'dissabte'];
        $mesos = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];
        $mes = $mesos[$date->format('n') - 1];
        $dia = $dias[$date->format('w')];
        $num = $date->format('d');
        $de = in_array($mes, ['abril', 'agost', 'octubre']) ? " d'" : ' de ';
        $year = $date->format('Y');

        return $dia . ', ' . $num . $de . $mes . ' del ' . $year;
    }
}
