<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use App\Entity\Forum;

/**
 * A Permission represents access for a user to perform a certain action within
 * a forum.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 */
class Permission
{
    const VIEW = "view";     //View forum content.
    const POST = "post";     //Create new threads.
    const REPLY = "reply";   //Reply to an existing thread.
    const GRANT = "grant";   //Give other users their permissions
    const REVOKE = "revoke"; //Remove their permissions from others

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $attribute;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="User", inversedBy="permissions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="permissions")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    public function getId()
    {
        return $this->id;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(Forum $forum): self
    {
        $this->forum = $forum;

        return $this;
    }
}
