<?php

namespace Forum9000\Timestamps;

trait TimestampedEntityTrait {
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ctime;
    
    public function getCtime(): ?\DateTime {
        return $this->ctime;
    }

    public function setCtime(\DateTime $time): self {
        $this->ctime = $time;

        return $this;
    }
}