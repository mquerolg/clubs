<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ShipmentsMiddle
 *
 * @ORM\Table(name="CLUBS_LOCALIZATION_SHIPMENT")
 * @ORM\Entity
 */
class ShipmentsMiddle
{
    /**
     * @var int
     *
     * @ORM\Column(name="tram_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tram_desc", type="string", length=45, nullable=false)
     */
    private $tramDesc;

    /**
     * @var Shipments
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Shipments")
     * @ORM\JoinTable(name="CLUBS_LOCALIZATION_SHIPMENT")
     * @ORM\JoinColumn(name="tram_desc", referencedColumnName="trm_ruta")
     */
    private $shipments;

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
     * Get the value of tramDesc
     *
     * @return  string
     */
    public function getTramDesc()
    {
        return $this->tramDesc;
    }

    /**
     * Set the value of tramDesc
     *
     * @param  string  $tramDesc
     *
     * @return  self
     */
    public function setTramDesc(string $tramDesc)
    {
        $this->tramDesc = $tramDesc;

        return $this;
    }

    /**
     * Get the value of shipments
     *
     * @return  ArrayCollection
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Set the value of shipments
     *
     * @param  ArrayCollection  $shipments
     *
     * @return  self
     */
    public function setShipments(ArrayCollection $shipments)
    {
        $this->shipments = $shipments;

        return $this;
    }
}
