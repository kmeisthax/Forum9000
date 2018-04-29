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
     * The authenticated user who made this post.
     * 
     * Groups can be listed as the creator of a post, but we want to be able to
     * determine the post's original creator for auditing purposes.
     * 
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $posting_user;
    
    /**
     * The actor who this post is identified as having posted.
     * 
     * Groups can be listed as the creator of a post, and all users should see
     * the group identity by default. A post made under group identity is also
     * treated as owned by the group and not the user who posted it to the
     * group.
     * 
     * @ORM\ManyToOne(targetEntity="Actor")
     */
    private $posted_by;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $ctime;
    
    /**
     * @ORM\Column(type="string", options={"default":"plaintext"}))
     */
    private $markupLanguage;

    public function __construct() {
        $this->ctime = new \DateTime();
    }
    
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

    public function getPostingUser(): ?User {
        return $this->posting_user;
    }

    public function setPostingUser(User $user): self {
        $this->posting_user = $user;

        return $this;
    }

    public function getPostedBy(): ?Actor {
        return $this->posted_by;
    }

    public function setPostedBy(Actor $actor): self {
        $this->posted_by = $actor;

        return $this;
    }

    public function getCtime(): ?\DateTime {
        return $this->ctime;
    }

    public function setCtime(\DateTime $time): self {
        $this->ctime = $time;

        return $this;
    }

    public function getMarkupLanguage(): ?string {
        return $this->markupLanguage;
    }

    public function setMarkupLanguage(string $time): self {
        $this->markupLanguage = $time;

        return $this;
    }
}
