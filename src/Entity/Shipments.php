<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shipments
 *
 * @ORM\Table(name="CLUBS_SHIPMENTS")
 * @ORM\Entity
 */
class Shipments
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="trm_ruta", type="string", length=45, nullable=false)
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="trm_lot", type="string", length=45, nullable=false)
     */
    private $lot;

    /**
     * @var string
     *
     * @ORM\Column(name="trm_end_date", type="string", length=45, nullable=false)
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="trm_start_date", type="string", length=45, nullable=false)
     */
    private $startDate;

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
     * Get the value of route
     *
     * @return  string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the value of route
     *
     * @param  string  $route
     *
     * @return  self
     */
    public function setRoute(string $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the value of lot
     *
     * @return  string
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set the value of lot
     *
     * @param  string  $lot
     *
     * @return  self
     */
    public function setLot(string $lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get the value of endDate
     *
     * @return  string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set the value of endDate
     *
     * @param  string  $endDate
     *
     * @return  self
     */
    public function setEndDate(string $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the value of startDate
     *
     * @return  string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set the value of startDate
     *
     * @param  string  $startDate
     *
     * @return  self
     */
    public function setStartDate(string $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }
}
