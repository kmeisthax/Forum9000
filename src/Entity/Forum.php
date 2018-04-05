<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForumRepository")
 */
class Forum
{
    use \App\CompactId\EntityTrait;
    
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

    public function __construct() {
        $this->threads = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->grants = new ArrayCollection();
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
}
