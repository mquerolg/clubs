<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shipments
 *
 * @ORM\Table(name="CLUBS_LOCALIZATIONS")
 * @ORM\Entity
 */
class Localizations
{
    /**
     * @var int
     *
     * @ORM\Column(name="loc_iii_id", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_codi_ens", type="string", length=255, nullable=false)
     */
    private $ens;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_ruta", type="string", length=255, nullable=false)
     */
    private $route;

    /**
     * @var ShipmentsMiddle
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ShipmentsMiddle")
     * @ORM\JoinColumn(name="loc_ruta", referencedColumnName="tram_id")
     */
    private $shipments;

    /**
     * @var int|null
     *
     * @ORM\Column(name="loc_inaugurada", type="integer", nullable=true)
     */
    private $active = 0;

    /**
     * Get the value of ens
     *
     * @return  string
     */
    public function getEns()
    {
        return $this->ens;
    }

    /**
     * Set the value of ens
     *
     * @param  string  $ens
     *
     * @return  self
     */
    public function setEns(string $ens)
    {
        $this->ens = $ens;

        return $this;
    }

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
     * Get the value of shipments
     *
     * @return  ShipmentsMiddle
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Set the value of shipments
     *
     * @param  ShipmentsMiddle  $shipments
     *
     * @return  self
     */
    public function setShipments(ShipmentsMiddle $shipments)
    {
        $this->shipments = $shipments;

        return $this;
    }

    /**
     * Get the value of active
     *
     * @return  bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @param  bool|null  $active
     *
     * @return  self
     */
    public function setActive($active)
    {
        $this->active = $active;

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
        if (is_null($this->shipments) || !method_exists($this->shipments, 'getTramDesc')) {
            return '';
        }

        return $this->shipments->getTramDesc() ?? '';
    }
}
