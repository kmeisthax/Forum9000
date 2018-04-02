<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 */
class Thread
{
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
}
