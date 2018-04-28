<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An Estate represents the target of a Permission or Grant.
 * 
 * Any entity may reference an Estate, which makes it a permissioned object
 * whose actions are controlled by the Permission/Grant system. The Estate ID
 * must match the referencing object's ID.
 * 
 * @ORM\Entity()
 */
class Estate {
    use \Forum9000\CompactId\EntityTrait;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;
    
    /**
     * PHP class which created this estate.
     * 
     * @ORM\Column(type="string")
     */
    private $classname;
    
    /**
     * The list of default permissions for the estate.
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="estate")
     */
    private $permissions;

    /**
     * The list of specific grants for particular actors.
     *
     * @ORM\OneToMany(targetEntity="Grant", mappedBy="estate")
     */
    private $grants;

    public function __construct($object) {
        $this->permissions = new ArrayCollection();
        $this->grants = new ArrayCollection();
        
        $this->classname = get_class($object);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getClass() : string {
        return $this->classname;
    }
    
    public function getPermissions() {
        return $this->permissions;
    }

    public function getGrants() {
        return $this->grants;
    }
}