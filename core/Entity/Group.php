<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Forum9000\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
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
    private $members;
    
    public function __construct() {
        parent::__construct();
        
        $this->ensureEstateExists();
        $this->ctime = new \DateTime();
        $this->members = new ArrayCollection();
    }
    
    public function getMembers() {
        return $this->members;
    }
}
