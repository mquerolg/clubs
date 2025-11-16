<?php

namespace App\Entity\Cruds;

use App\Entity\Entity;
use App\Entity\Support\Authorship;
use App\Entity\Support\LangSum;
use App\Entity\Support\Owners;
use App\Entity\Support\Signatures;
use App\Entity\Support\Status;
use App\Entity\Traits\HistoricEntityTray;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Incidences
 *
 * @ORM\Table(name="CLUBS_HISTORIC")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Incidences extends Entity
{
    use SoftDeleteableEntity;
    use HistoricEntityTray;

    /**
     * @var Authorship
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Authorship")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $authorship;

    /**
     * @var LangSum
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\LangSum")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $langSum;

    /**
     * @var Owners
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Owners")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var Signatures
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Signatures")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $signature;

    /**
     * @var Status
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\Status")
     * @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     */
    private $status;

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of authorship
     *
     * @return  Authorship
     */
    public function getAuthorship()
    {
        return $this->authorship;
    }

    /**
     * Get the value of langSum
     *
     * @return  LangSum
     */
    public function getLangSum()
    {
        return $this->langSum;
    }

    /**
     * Get the value of owner
     *
     * @return  Owners
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get the value of signature
     *
     * @return  Signatures
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Get the value of status
     *
     * @return  Status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
