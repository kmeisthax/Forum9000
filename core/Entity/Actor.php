<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class", type="string")
 */
class Actor {
    use \Forum9000\CompactId\EntityTrait;
    use \Forum9000\Timestamps\TimestampedEntityTrait;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string")
     */
    protected $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Grant", mappedBy="user")
     */
    private $grants;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $handle;
    
    /**
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="member")
     */
    private $memberships;
    
    public function __construct() {
        $this->grants = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->ctime = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getGrants() {
        return $this->grants;
    }
    
    public function getMemberships() {
        return $this->memberships;
    }

    public function getHandle(): ?string {
        return $this->handle;
    }

    public function setHandle(string $handle): self {
        $this->handle = $handle;

        return $this;
    }
}