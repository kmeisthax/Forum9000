<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 */
class Group extends Actor {
    use \Forum9000\EstateSecurity\EstateBearingEntityTrait;
    
    /**
     * @ORM\OneToOne(targetEntity="Estate", cascade={"persist"})
     * @ORM\JoinColumn(name="estate", referencedColumnName="id")
     */
    private $estate;
    
    /**
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="group")
     */
    private $memberships;
    
    public function __construct() {
        parent::__construct();
        
        $this->ensureEstateExists();
        $this->ctime = new DateTime();
        $this->memberships = new ArrayCollection();
    }
    
    public function getMemberships() {
        return $this->memberships;
    }
}