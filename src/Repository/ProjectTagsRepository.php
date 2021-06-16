<?php

namespace App\Repository;

use App\Entity\ProjectTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Projects|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projects|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projects[]    findAll()
 * @method Projects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectTagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectTags::class);
    }

    public function queryAllProjectsByTag($seoLink): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('b')
            ->where('tags.tag.seoLink = :seoLink')
            ->orderBy('b.modified', 'DESC')
            ->addOrderBy('b.created', 'DESC')
            ->setParameter('seoLink', $seoLink)
            ->getQuery();
    }
}
