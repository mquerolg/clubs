<?php

namespace App\Entity\Deleted;

use App\Entity\Entity;
use App\Entity\Traits\LotsEntityTray;
use Doctrine\ORM\Mapping as ORM;

/**
 * LotsDeleted
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 */
class LotsDeleted extends Entity
{
    use LotsEntityTray;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="sillydatetime", nullable=true)
     */
    protected $deletedAt;

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of deletedAt
     *
     * @return  \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set or clear the deleted at timestamp.
     *
     * @return self
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of delete status
     *
     * @return  string
     */
    public function getDeletedStatus()
    {
        return '--';
    }
}
