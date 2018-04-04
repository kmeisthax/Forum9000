<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Forum;
use App\Entity\User;

/**
 * Represents a specific right, or denial of a right, for a particular
 * authenticated user to perform an action on a forum.
 *
 * @ORM\Entity(repositoryClass="App\Repository\GrantRepository")
 */
class Grant
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $attribute;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="grants")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="User", inversedBy="grants")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * TRUE if and only if the user is allowed to perform this action.
     * FALSE implies tri-state, not denied.
     *
     * @ORM\Column(type="boolean")
     */
    private $isGranted;

    /**
     * TRUE if and only if the user is prohibited from performing this action.
     * Can be used to override otherwise authorized actions in complicated
     * permissions scenarios.
     * FALSE implies tri-state, not granted.
     *
     * @ORM\Column(type="boolean")
     */
    private $isDenied;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsGranted(): ?bool
    {
        return $this->isGranted;
    }

    public function setIsGranted(bool $isGranted): self
    {
        $this->isGranted = $isGranted;

        return $this;
    }

    public function getIsDenied(): ?bool
    {
        return $this->isDenied;
    }

    public function setIsDenied(bool $isDenied): self
    {
        $this->isDenied = $isDenied;

        return $this;
    }
}
