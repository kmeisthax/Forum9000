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
     * @ORM\OneToOne(targetEntity="Estate")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $estate;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;
    
    /**
     * A user-friendly short title suitable for inclusion in a URL.
     * 
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $slug;
    
    /**
     * Order of forums within the parent forum.
     * 
     * @ORM\Column(name="`order`", type="integer", options={"default":0})
     */
    private $order;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="forum")
     */
    private $threads;
    
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
        $this->subforums = new ArrayCollection();
        $this->estate = new Estate($this);
    }

    public function getId()
    {
        return $this->getEstate()->getId();
    }

    public function getEstate(): ?Estate
    {
        return $this->estate;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getThreads() {
        return $this->threads;
    }
    
    public function getSubforums() {
        return $this->subforums;
    }

    public function getParent(): ?Forum
    {
        return $this->parent;
    }

    public function setParent(?Forum $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
