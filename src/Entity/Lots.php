<?php

namespace App\Entity;

use App\Entity\Traits\LotsEntityTray;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Lots
 *
 * @ORM\Table(name="CLUBS_LOTS")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Lots extends Entity
{
    use SoftDeleteableEntity;
    use LotsEntityTray;

    /**
     * Get the value of bibliogafic,
     * use in lots controller to readonly field
     *
     * @return  string
     */
    public function getReadBibliographic()
    {
        return $this->bibliographic;
    }
}
