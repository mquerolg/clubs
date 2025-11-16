<?php

namespace App\Entity\Reports;

use App\Entity\Entity;
use App\Entity\Traits\HistoricEntityTray;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * HistoricReport
 *
 * @ORM\Table(name="CLUBS_HISTORIC")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class HistoricReport extends Entity
{
    use SoftDeleteableEntity;
    use HistoricEntityTray;
}
