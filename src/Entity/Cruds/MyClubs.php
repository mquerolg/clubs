<?php

namespace App\Entity\Cruds;

use App\Entity\Entity;
use App\Entity\Traits\ClubsEntityTray;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * MyClubs
 *
 * @ORM\Table(name="CLUBS_CLUBS")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class MyClubs extends Entity
{
    use SoftDeleteableEntity;
    use ClubsEntityTray;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->year,
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private $dataHistory = [];

    public function getDataHistory(): ?array
    {
        return $this->dataHistory;
    }

    public function setDataHistory(?array $dataHistory): self
    {
        $this->dataHistory = $dataHistory;

        return $this;
    }
    
    private $library;

    public function getLibrary(): ?Libraries
    {
        return $this->library;
    }

    public function setLibrary(?Libraries $library): self
    {
        $this->library = $library;
        return $this;
    }
}
