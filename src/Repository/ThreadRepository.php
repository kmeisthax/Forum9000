<?php

namespace App\Repository;

use App\Entity\Thread;
use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use StephenHill\Base58;

/**
 * @method Thread|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thread|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thread[]    findAll()
 * @method Thread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Thread::class);
    }
    
    public function findByCompactId(string $id) {
        $raw_uuid = (new Base58())->decode($id);
        $uuid = "";

        foreach (str_split($raw_uuid) as $char) {
            $hexit = strtoupper(dechex(ord($char)));
            while (strlen($hexit) < 2) $hexit = "0" . $hexit;
            $uuid .= strrev($hexit);

            if (strlen($uuid) == 8) $uuid .= "-";
            else if (strlen($uuid) == 13) $uuid .= "-";
            else if (strlen($uuid) == 18) $uuid .= "-";
            else if (strlen($uuid) == 23) $uuid .= "-";
        }

        return $this->find($uuid);
    }

    public function getLatestThreads($start = 0, $limit = 1) {
        return $this->createQueryBuilder('t')
            ->join('t.posts', 'p')
            ->orderBy('p.ctime', 'DESC')
            ->groupBy('t')
            ->getQuery()
            ->getResult();
    }
    
    public function getLatestThreadsInForum(Forum $f, $start = 0, $limit = 1) {
        return $this->createQueryBuilder('t')
            ->where('t.forum = :forum_id')->setParameter("forum_id", $f->getId())
            ->join('t.posts', 'p')
            ->orderBy('p.ctime', 'DESC')
            ->groupBy('t')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Thread[] Returns an array of Thread objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Thread
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
