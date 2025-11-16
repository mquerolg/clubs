<?php

namespace App\Entity;

use App\Entity\Traits\ClubsEntityTray;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Clubs
 *
 * @ORM\Table(name="CLUBS_CLUBS")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Clubs extends Entity
{
    use SoftDeleteableEntity;
    use ClubsEntityTray;
    private $dataHistory;

    public function getRoute()
    {
        return (!is_null($this->library) && method_exists($this->library, 'getLocalization')) ?
                $this->library->getLocalization()->__toString()
            :
                ''
        ;
    }

    // Agrega el getter para dataHistory
    public function getDataHistory(): ?string
    {
        return $this->dataHistory;
    }

    // Agrega el setter para dataHistory
    public function setDataHistory(?string $dataHistory): self
    {
        $this->dataHistory = $dataHistory;
        return $this;
    }
}
