<?php

namespace Forum9000\Repository;

use Forum9000\Entity\Forum;
use Forum9000\Entity\Thread;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    use \Forum9000\CompactId\RepositoryTrait;
    
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Forum::class);
    }
    
    public function findBySlug(string $slug) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();
    }
    
    /**
     * Given a forum, return child subforums and threads, sorted by pin order
     * and then newest post date.
     * 
     * Returns an array of forum and thread objects.
     */
    public function getForumChildren(Forum $f, $start = 0, $limit = 1) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('id', 'id');
        
        $nQuery = $this->_em->createNativeQuery("
SELECT * FROM
	(SELECT :forum_class as 'type', f.id as 'id', f.`order` as 'order', p.ctime as 'ctime'
				FROM forum as f LEFT OUTER JOIN thread as t ON f.id = t.forum_id LEFT OUTER JOIN post as p ON t.id = p.thread_id
                WHERE f.parent_id = :forum_id
		UNION SELECT :thread_class as 'type', t.id as 'id', t.`order`, p.ctime as ctime
				 FROM thread as t INNER JOIN post as p ON t.id = p.thread_id
                 WHERE t.forum_id = :forum_id ORDER BY `order` DESC, ctime DESC) as q
	GROUP BY id ORDER BY `order` DESC, ctime DESC LIMIT :limit OFFSET :offset;", $rsm);
        $nQuery->setParameter(":forum_class", Forum::class);
        $nQuery->setParameter(":thread_class", Thread::class);
        $nQuery->setParameter(":forum_id", $f->getId());
        $nQuery->setParameter(":limit", $limit);
        $nQuery->setParameter(":offset", $start);
        
        //Manually map the results to Doctrine objects by... just issuing two
        //more queries for all the other objects we want, and then sorting them
        //into an array according to the result set
        $resultRows = $nQuery->getResult();
        $keys = array(Forum::class => array(), Thread::class => array());
        
        foreach ($resultRows as $sqlRow) {
            $keys[$sqlRow["type"]][] = $sqlRow["id"];
        }
        
        $objectsById = array();
        
        foreach ($keys as $doctrineClass => $keylist) {
            if (count($keylist) === 0) continue;
            
            $repository = $this->_em->getRepository($doctrineClass);
            $dQuery = $repository->createQueryBuilder('k');
            $dQuery->where($dQuery->expr()->in('k.id', $keylist));
            $objects = $dQuery->getQuery()->getResult();
            
            foreach ($objects as $object) {
                $objectsById[$object->getId()] = $object;
            }
        }
        
        $result = array();
        
        foreach ($resultRows as $sqlRow) {
            $result[] = $objectsById[$sqlRow["id"]];
        }
        
        return $result;
    }
    
    /**
     * Returns a count of all forum and thread objects which are children of a
     * given parent forum.
     */
    public function getForumChildCount(Forum $f) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');
        
        $nQuery = $this->_em->createNativeQuery("
SELECT COUNT(DISTINCT id) as 'count' FROM
	(SELECT f.id as 'id', f.`order` as 'order', p.ctime as 'ctime'
				FROM forum as f LEFT OUTER JOIN thread as t ON f.id = t.forum_id LEFT OUTER JOIN post as p ON t.id = p.thread_id
                WHERE f.parent_id = :forum_id
		UNION SELECT t.id as 'id', t.`order`, p.ctime as ctime
				 FROM thread as t INNER JOIN post as p ON t.id = p.thread_id
                 WHERE t.forum_id = :forum_id) as q;", $rsm);
        $nQuery->setParameter(":forum_id", $f->getId());
        
        return $nQuery->getSingleResult()["count"];
    }
    
    /**
     * Returns a count of all subforums within a forum.
     */
    public function getForumSubforumCount(Forum $f) {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.parent = :forum_id')->setParameter("forum_id", $f->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Forum[] Returns an array of Forum objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Forum
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
