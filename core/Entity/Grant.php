<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;

use Forum9000\Entity\Estate;
use Forum9000\Entity\User;

/**
 * Represents a specific right, or denial of a right, for a particular
 * authenticated user to perform an action on an estate (e.g. forum, group, etc)
 *
 * @ORM\Entity(repositoryClass="Forum9000\Repository\GrantRepository")
 * @ORM\Table(name="`grant`")
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
     * @ORM\ManyToOne(targetEntity="Estate", inversedBy="grants")
     * @ORM\JoinColumn(name="estate_id", referencedColumnName="id")
     */
    private $estate;

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

    public function getEstate(): ?Estate
    {
        return $this->estate;
    }

    public function setEstate(Estate $estate): self
    {
        $this->estate = $estate;

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
    
    public function getGrantStatus(): ?bool {
        if ($this->isGranted) return true;
        if ($this->isDenied) return false;
        return null;
    }
    
    public function setGrantStatus(?bool $grantStatus) {
        print json_encode($grantStatus);
        if ($grantStatus === null) {
            $this->isGranted = false;
            $this->isDenied = false;
        } else {
            $this->isGranted = $grantStatus;
            $this->isDenied = !$grantStatus;
        }
        
        return $this;
    }
}
