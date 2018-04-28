<?php

namespace Forum9000\EstateSecurity;

use Doctrine\ORM\Mapping as ORM;

use Forum9000\Entity\Estate;

trait EstateBearingEntityTrait {
    /**
     * @ORM\Id()
     * @ORM\OneToOne(targetEntity="Estate", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $id;
    
    private function ensureEstateExists() {
        $this->id = new Estate($this);
    }

    public function getEstate(): ?Estate
    {
        return $this->id;
    }
    
    public function getPermissions() {
        return $this->getEstate()->getPermissions();
    }

    public function getGrants() {
        return $this->getEstate()->getGrants();
    }
}