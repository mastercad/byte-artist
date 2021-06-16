<?php

namespace App\Repository;

use App\Entity\Projects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Projects|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projects|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projects[]    findAll()
 * @method Projects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projects::class);
    }

    public function findLatest(int $firstResult = 0, int $maxResults = 25)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created', 'DESC')
            ->setFirstResult($firstResult ?: null)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNewest(int $limit = 0)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryAllVisibleProjects(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.isPublic = 1')
            ->orderBy('p.created', 'DESC')
            ->addOrderBy('p.modified', 'DESC')
            ->getQuery();
    }

    public function queryAllProjectsByTag($seoLink): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('App\Entity\ProjectTags', 'pt', Join::WITH, 'pt.project = p')
            ->innerJoin('App\Entity\Tags', 't', Join::WITH, 't = pt.tag')
            ->where('t.seoLink = :seoLink')
            ->orderBy('p.modified', 'DESC')
            ->addOrderBy('p.created', 'DESC')
            ->setParameter('seoLink', $seoLink)
            ->getQuery();
    }
}
