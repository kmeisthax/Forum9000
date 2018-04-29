<?php

namespace Forum9000\EstateSecurity;

use Doctrine\ORM\Mapping as ORM;

use Forum9000\Entity\Estate;

trait EstateBearingEntityTrait {
    private function ensureEstateExists() {
        $this->estate = new Estate($this);
    }

    public function getEstate(): ?Estate {
        return $this->estate;
    }
    
    public function getPermissions() {
        return $this->getEstate()->getPermissions();
    }

    public function getGrants() {
        return $this->getEstate()->getGrants();
    }
}
