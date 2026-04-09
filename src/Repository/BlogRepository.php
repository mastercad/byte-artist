<?php

namespace App\Repository;

use App\Entity\Blogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blogs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blogs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blogs[]    findAll()
 * @method Blogs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blogs::class);
    }

    public function findLatest(int $firstResult = 0, int $maxResults = 25)
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.created', 'DESC')
            ->addOrderBy('b.modified', 'DESC')
            ->setFirstResult($firstResult ?: null)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNewest(int $limit = 0)
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.created', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryAllVisibleBlogs(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('b')
            ->where('b.isPublic = 1')
            ->orderBy('b.created', 'DESC')
            ->addOrderBy('b.modified', 'DESC')
            ->getQuery();
    }

    public function queryAllBlogs(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.created', 'DESC')
            ->addOrderBy('b.modified', 'DESC')
            ->getQuery();
    }

    public function queryVisibleBlogsByTag($seoLink): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('App\\Entity\\BlogTags', 'bt', Join::WITH, 'bt.blog = b')
            ->innerJoin('App\\Entity\\Tags', 't', Join::WITH, 't = bt.tag')
            ->where('t.seoLink = :seoLink')
            ->andWhere('b.isPublic = 1')
            ->orderBy('b.created', 'DESC')
            ->addOrderBy('b.modified', 'DESC')
            ->setParameter('seoLink', $seoLink)
            ->getQuery();
    }

    public function queryAllBlogsByTag($seoLink): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('App\Entity\BlogTags', 'bt', Join::WITH, 'bt.blog = b')
            ->innerJoin('App\Entity\Tags', 't', Join::WITH, 't = bt.tag')
            ->where('t.seoLink = :seoLink')
            ->orderBy('b.created', 'DESC')
            ->addOrderBy('b.modified', 'DESC')
            ->setParameter('seoLink', $seoLink)
            ->getQuery();
    }
}
