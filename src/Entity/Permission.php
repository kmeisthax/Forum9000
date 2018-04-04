<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use App\Entity\Forum;

/**
 * A Permission represents a specific action performed on a particular forum.
 * Grants are specified for authenticated and anonymous users.
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
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="permissions")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    /**
     * TRUE if permission is granted to logged-in users.
     * Only checked for authenticated users without a specific grant.
     *
     * @ORM\Column(type="boolean")
     */
    private $isGrantedAuth;

    /**
     * TRUE if permission is granted to anonymous users.
     * Only checked for anonymous users without a specific grant.
     * You should be extremely careful with anonymous grants.
     *
     * @ORM\Column(type="boolean")
     */
    private $isGrantedAnon;

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): self
    {
        $this->attribute = $attribute;

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

    public function getIsGrantedAuth(): ?bool
    {
        return $this->isGrantedAuth;
    }

    public function setIsGrantedAuth(bool $isGrantedAuth): self
    {
        $this->isGrantedAuth = $isGrantedAuth;

        return $this;
    }

    public function getIsGrantedAnon(): ?bool
    {
        return $this->isGrantedAnon;
    }

    public function setIsGrantedAnon(bool $isGrantedAnon): self
    {
        $this->isGrantedAnon = $isGrantedAnon;

        return $this;
    }
}
