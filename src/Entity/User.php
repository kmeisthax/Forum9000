<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
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

    //UserInterface
    public function getRoles(): array {
        //We don't use Symfony roles that much...
        //ROLE_USER means you're logged in
        //ROLE_ADMIN means you can do anything aside from site-bricking actions
        //ROLE_DEVELOPER means you can brick the site and we won't care
        return array("ROLE_USER");
    }

    public function getSalt(): ?string {
        return null;
    }

    public function getUsername(): string {
        //Forum9000 users are authenticated by e-mail address.
        //They name themselves with changeable handles.
        return $this->getEmail;
    }

    public function eraseCredentials() {
        //Does nothing.
    }

    //Serializable
    public function serialize() {
        return serialize(
            $this->id,
            $this->email,
            $this->password
        );
    }

    public function unserialize($serialized) {
        list(
            $this->id,
            $this->email,
            $this->password
        ) = unserialize($serialized);
    }
}
