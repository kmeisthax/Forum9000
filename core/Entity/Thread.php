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
}