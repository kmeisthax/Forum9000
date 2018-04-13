<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass="Forum9000\Repository\ThreadRepository")
 */
class Thread
{
    use \Forum9000\CompactId\EntityTrait;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="thread")
     */
    private $posts;
    
    /**
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="threads")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;
    
    /**
     * True if the thread is locked. A locked thread cannot be replied to,
     * unless the user has a special permission for replying to locked threads.
     * 
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isLocked;
    
    /**
     * Order of forum threads within the parent forum.
     * 
     * @ORM\Column(name="`order`", type="integer", options={"default":0})
     */
    private $order;

    public function __construct() {
        $this->posts = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getPosts() {
        return $this->posts;
    }

    public function getOrderedPosts($start = 0, $limit = 1) {
        $orderedPostCriteria = Criteria::create()
            ->orderBy(array("order" => Criteria::ASC))
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $this->getPosts()->matching($orderedPostCriteria);
    }

    public function getNewestPosts($start = 0, $limit = 1) {
        $newestPostCriteria = Criteria::create()
            ->orderBy(array("order" => Criteria::DESC))
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $this->getPosts()->matching($newestPostCriteria);
    }
    
    public function getForum() : ?Forum {
        return $this->forum;
    }
    
    public function setForum(Forum $forum) : self {
        $this->forum = $forum;
        
        return $this;
    }
    
    public function getIsLocked() : ?bool {
        return $this->isLocked;
    }
    
    public function setIsLocked(bool $isLocked) : self {
        $this->isLocked = $isLocked;
        
        return $this;
    }
    
    public function getOrder() : ?int {
        return $this->order;
    }
    
    public function setOrder(int $order) : self {
        $this->order = $order;
        
        return $this;
    }
}
