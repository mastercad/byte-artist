<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\ProjectsRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProjectsRepository that verify query-builder behaviour
 * without a real database.
 */
class ProjectsRepositoryTest extends TestCase
{
    // ------------------------------------------------------------------ findLatest

    public function testFindLatestWithZeroFirstResultPassesNullToSetFirstResult(): void
    {
        // 0 ?: null → null  (key edge case — treats 0 as "no offset")
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setFirstResult')
            ->with(null)
            ->willReturnSelf();

        $repo->findLatest(0, 10);
    }

    public function testFindLatestWithPositiveFirstResultPassesThatValue(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setFirstResult')
            ->with(10)
            ->willReturnSelf();

        $repo->findLatest(10, 25);
    }

    public function testFindLatestSetsMaxResultsFromArgument(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setMaxResults')
            ->with(50)
            ->willReturnSelf();

        $repo->findLatest(0, 50);
    }

    public function testFindLatestOrdersByCreatedDesc(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('p.created', 'DESC')
            ->willReturnSelf();

        $repo->findLatest(0, 10);
    }

    public function testFindLatestDefaultParametersUseZeroAndTwentyFive(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setFirstResult')
            ->with(null) // 0 ?: null
            ->willReturnSelf();
        $mockQb->expects(self::once())
            ->method('setMaxResults')
            ->with(25)
            ->willReturnSelf();

        $repo->findLatest(); // uses defaults
    }

    // ------------------------------------------------------------------ findNewest

    public function testFindNewestSetsMaxResultsToLimit(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setMaxResults')
            ->with(3)
            ->willReturnSelf();

        $repo->findNewest(3);
    }

    public function testFindNewestOrdersByCreatedDesc(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('p.created', 'DESC')
            ->willReturnSelf();

        $repo->findNewest(5);
    }

    public function testFindNewestWithZeroLimitSetsMaxResultsZero(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setMaxResults')
            ->with(0)
            ->willReturnSelf();

        $repo->findNewest(0);
    }

    // ------------------------------------------------------------------ queryAllVisibleProjects

    public function testQueryAllVisibleProjectsAddsIsPublicOneFilter(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('where')
            ->with('p.isPublic = 1')
            ->willReturnSelf();

        $repo->queryAllVisibleProjects();
    }

    public function testQueryAllVisibleProjectsOrdersByCreatedThenModified(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('p.created', 'DESC')
            ->willReturnSelf();
        $mockQb->expects(self::once())
            ->method('addOrderBy')
            ->with('p.modified', 'DESC')
            ->willReturnSelf();

        $repo->queryAllVisibleProjects();
    }

    public function testQueryAllVisibleProjectsReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryAllVisibleProjects();

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ queryAllProjects

    public function testQueryAllProjectsHasNoVisibilityFilter(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        // where() must NOT be called — no visibility filter in queryAllProjects
        $mockQb->expects(self::never())
            ->method('where');

        $repo->queryAllProjects();
    }

    public function testQueryAllProjectsOrdersByCreatedThenModified(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('p.created', 'DESC')
            ->willReturnSelf();
        $mockQb->expects(self::once())
            ->method('addOrderBy')
            ->with('p.modified', 'DESC')
            ->willReturnSelf();

        $repo->queryAllProjects();
    }

    public function testQueryAllProjectsReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryAllProjects();

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ queryAllProjectsByTag

    public function testQueryAllProjectsByTagFiltersOnSeoLink(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setParameter')
            ->with('seoLink', 'php')
            ->willReturnSelf();

        $repo->queryAllProjectsByTag('php');
    }

    public function testQueryAllProjectsByTagAddsWhereCondition(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('where')
            ->with('t.seoLink = :seoLink')
            ->willReturnSelf();

        $repo->queryAllProjectsByTag('symfony');
    }

    public function testQueryAllProjectsByTagHasNoIsPublicFilter(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        // andWhere must NOT be called — no visibility filter in queryAllProjectsByTag
        $mockQb->expects(self::never())
            ->method('andWhere');

        $repo->queryAllProjectsByTag('php');
    }

    public function testQueryAllProjectsByTagJoinsRelations(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::atLeast(2))
            ->method('innerJoin')
            ->willReturnSelf();

        $repo->queryAllProjectsByTag('php');
    }

    public function testQueryAllProjectsByTagReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryAllProjectsByTag('php');

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ queryVisibleProjectsByTag

    public function testQueryVisibleProjectsByTagFiltersOnSeoLink(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setParameter')
            ->with('seoLink', 'php')
            ->willReturnSelf();

        $repo->queryVisibleProjectsByTag('php');
    }

    public function testQueryVisibleProjectsByTagAddsIsPublicFilter(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('andWhere')
            ->with('p.isPublic = 1')
            ->willReturnSelf();

        $repo->queryVisibleProjectsByTag('php');
    }

    public function testQueryVisibleProjectsByTagAddsWhereConditionForSeoLink(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('where')
            ->with('t.seoLink = :seoLink')
            ->willReturnSelf();

        $repo->queryVisibleProjectsByTag('symfony');
    }

    public function testQueryVisibleProjectsByTagReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryVisibleProjectsByTag('php');

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Returns [repository-partial-mock, queryBuilder-mock].
     *
     * @return array{ProjectsRepository, MockObject&QueryBuilder}
     */
    private function makeRepo(): array
    {
        /** @var MockObject&Query $mockQuery */
        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();
        $mockQuery->method('getResult')->willReturn([]);

        /** @var MockObject&QueryBuilder $mockQb */
        $mockQb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockQb->method('orderBy')->willReturnSelf();
        $mockQb->method('addOrderBy')->willReturnSelf();
        $mockQb->method('setFirstResult')->willReturnSelf();
        $mockQb->method('setMaxResults')->willReturnSelf();
        $mockQb->method('innerJoin')->willReturnSelf();
        $mockQb->method('where')->willReturnSelf();
        $mockQb->method('andWhere')->willReturnSelf();
        $mockQb->method('setParameter')->willReturnSelf();
        $mockQb->method('getQuery')->willReturn($mockQuery);

        /** @var MockObject&ProjectsRepository $repo */
        $repo = $this->getMockBuilder(ProjectsRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        $repo->method('createQueryBuilder')->willReturn($mockQb);

        return [$repo, $mockQb];
    }
}
