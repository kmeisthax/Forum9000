<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    use \App\CompactId\EntityTrait;

    const USER = "ROLE_USER";
    const STAFF = "ROLE_STAFF";
    const DEVELOPER = "ROLE_DEVELOPER";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $handle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="Grant", mappedBy="user")
     */
    private $grants;
    
    /**
     * Get the user's current site role.
     * 
     * We have our own access control mechanisms, so roles are only necessary
     * to manage site-wide access that operate outside of the normal permissions
     * and grants system.
     * 
     * Most users will be ROLE_USER, which means you have no special site-wide
     * access.
     * 
     * Access to the administrative backend requires ROLE_STAFF. Staff can use
     * the administrative backend to bypass normal access control. They can only
     * excercise these rights from within the administrative backend.
     * 
     * Certain site-wide controls are only appropriate for technically minded
     * users, and thus requires ROLE_DEVELOPER. Developers are additionally
     * given access to things like custom database queries, theming options,
     * and other things not appropriate for even normal staff usage. Assign this
     * role with extreme caution.
     * 
     * @ORM\Column(type="string", length=255, options={"default":"ROLE_USER"})
     */
    private $site_role;

    public function __construct() {
        $this->grants = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getHandle(): ?string {
        return $this->handle;
    }

    public function setHandle(string $handle): self {
        $this->handle = $handle;

        return $this;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    public function getSiteRole(): ?string {
        return $this->site_role;
    }

    public function setSiteRole(string $site_role): self {
        $this->site_role = $site_role;

        return $this;
    }

    public function getGrants() {
        return $this->grants;
    }

    //UserInterface
    public function getRoles(): array {
        return array($this->getSiteRole());
    }

    public function getSalt(): ?string {
        return null;
    }

    public function getUsername(): string {
        //Forum9000 users are authenticated by e-mail address.
        //They name themselves with changeable handles.
        return $this->getEmail();
    }

    public function eraseCredentials() {
        //Does nothing.
    }

    //Serializable
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password
        ));
    }

    public function unserialize($serialized) {
        list(
            $this->id,
            $this->email,
            $this->password
        ) = unserialize($serialized);
    }
}
