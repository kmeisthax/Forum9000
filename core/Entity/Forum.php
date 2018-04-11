<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Forum9000\Repository\ForumRepository")
 */
class Forum
{
    use \Forum9000\CompactId\EntityTrait;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="forum")
     */
    private $threads;

    /**
     * The list of default permissions for the board.
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="forum")
     */
    private $permissions;

    /**
     * The list of specific grants for particular users.
     *
     * @ORM\OneToMany(targetEntity="Grant", mappedBy="forum")
     */
    private $grants;
    
    /**
     * The list of subforums for a particular forum.
     * 
     * @ORM\OneToMany(targetEntity="Forum", mappedBy="parent")
     */
    private $subforums;
    
    /**
     * The parent forum for this forum.
     * 
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="subforums")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    public function __construct() {
        $this->threads = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->grants = new ArrayCollection();
        $this->subforums = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getThreads() {
        return $this->threads;
    }

    public function getPermissions() {
        return $this->permissions;
    }

    public function getGrants() {
        return $this->grants;
    }

    public function getSubforums() {
        return $this->subforums;
    }

    public function getParent(): ?Forum
    {
        return $this->parent;
    }

    public function setParent(Forum $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
