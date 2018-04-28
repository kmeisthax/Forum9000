<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;

use Forum9000\Entity\User;
use Forum9000\Entity\Estate;

/**
 * A Permission represents a specific action performed on a particular estate.
 * Grants are specified for authenticated and anonymous users.
 *
 * @ORM\Entity(repositoryClass="Forum9000\Repository\PermissionRepository")
 */
class Permission
{
    const VIEW = "view";     //View forum content.
    const POST = "post";     //Create new threads.
    const REPLY = "reply";   //Reply to an existing thread.
    const LOCK = "lock";     //Lock a thread, prohibiting further replies.
    const GRANT = "grant";   //Give other users their permissions
    const REVOKE = "revoke"; //Remove their permissions from others

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $attribute;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Estate", inversedBy="permissions")
     * @ORM\JoinColumn(name="estate_id", referencedColumnName="id")
     */
    private $estate;

    /**
     * TRUE if permission is granted to logged-in users.
     * FALSE means check the denied variable.
     * Only checked for authenticated users without a specific grant.
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isGrantedAuth;

    /**
     * TRUE if permission is granted to anonymous users.
     * FALSE means check the denied variable.
     * Only checked for anonymous users without a specific grant.
     * You should be extremely careful with anonymous grants.
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isGrantedAnon;

    /**
     * TRUE if permission is denied to logged-in users.
     * FALSE means check the parent forum's permissions, if one exists.
     * Only checked for authenticated users without a specific grant.
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isDeniedAuth;

    /**
     * TRUE if permission is denied to anonymous users.
     * FALSE means check the parent forum's permissions, if one exists.
     * Only checked for anonymous users without a specific grant.
     * You should be extremely careful with anonymous grants.
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isDeniedAnon;

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

    public function getIsDeniedAuth(): ?bool
    {
        return $this->isDeniedAuth;
    }

    public function setIsDeniedAuth(bool $isDeniedAuth): self
    {
        $this->isDeniedAuth = $isDeniedAuth;

        return $this;
    }

    public function getIsDeniedAnon(): ?bool
    {
        return $this->isDeniedAnon;
    }

    public function setIsDeniedAnon(bool $isDeniedAnon): self
    {
        $this->isDeniedAnon = $isDeniedAnon;

        return $this;
    }
}
