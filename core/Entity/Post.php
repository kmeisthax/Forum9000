<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Forum9000\Repository\PostRepository")
 */
class Post
{
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
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="posts")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id")
     */
    private $thread;

    /**
     * @ORM\Column(type="integer", name="et_order")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $posted_by;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $ctime;
    
    public function getId() {
        return $this->id;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): self {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function setMessage(string $message): self {
        $this->message = $message;

        return $this;
    }

    public function getThread(): ?Thread {
        return $this->thread;
    }

    public function setThread(Thread $thread): self {
        $this->thread = $thread;

        return $this;
    }

    public function getOrder() : ?int {
        return $this->order;
    }

    public function setOrder(int $order): self {
        $this->order = $order;

        return $this;
    }

    public function getPostedBy(): ?User {
        return $this->posted_by;
    }

    public function setPostedBy(User $user): self {
        $this->posted_by = $user;

        return $this;
    }

    public function getCtime(): ?\DateTime {
        return $this->ctime;
    }

    public function setCtime(\DateTime $time): self {
        $this->ctime = $time;

        return $this;
    }
}
