<?php

namespace App\Entity\Deleted;

use App\Entity\Entity;
use App\Entity\Traits\HistoricEntityTray;
use Doctrine\ORM\Mapping as ORM;

/**
 * Historic
 *
 * @ORM\Table(name="CLUBS_HISTORIC")
 * @ORM\Entity
 */
class HistoricDeleted extends Entity
{
    use HistoricEntityTray;

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
}
