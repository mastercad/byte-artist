<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\BlogRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BlogRepository that verify query-builder behaviour
 * without a real database.  The repository is partially mocked so that
 * createQueryBuilder() returns a configurable QueryBuilder mock; all
 * other methods run as usual.
 */
class BlogRepositoryTest extends TestCase
{
    // ------------------------------------------------------------------ findLatest

    public function testFindLatestWithZeroFirstResultPassesNullToSetFirstResult(): void
    {
        // 0 ?: null → null  (the key edge case)
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
            ->with(5)
            ->willReturnSelf();

        $repo->findLatest(5, 10);
    }

    public function testFindLatestSetsMaxResultsFromArgument(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setMaxResults')
            ->with(25)
            ->willReturnSelf();

        $repo->findLatest(0, 25);
    }

    public function testFindLatestOrdersByModifiedDescThenCreatedDesc(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('b.modified', 'DESC')
            ->willReturnSelf();
        $mockQb->expects(self::once())
            ->method('addOrderBy')
            ->with('b.created', 'DESC')
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
            ->with(5)
            ->willReturnSelf();

        $repo->findNewest(5);
    }

    public function testFindNewestOrdersByCreatedDesc(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('b.created', 'DESC')
            ->willReturnSelf();

        $repo->findNewest(3);
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

    // ------------------------------------------------------------------ queryAllVisibleBlogs

    public function testQueryAllVisibleBlogsOrdersByModifiedThenCreated(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('orderBy')
            ->with('b.modified', 'DESC')
            ->willReturnSelf();
        $mockQb->expects(self::once())
            ->method('addOrderBy')
            ->with('b.created', 'DESC')
            ->willReturnSelf();

        $repo->queryAllVisibleBlogs();
    }

    public function testQueryAllVisibleBlogsReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryAllVisibleBlogs();

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ queryAllBlogsByTag

    public function testQueryAllBlogsByTagFiltersOnSeoLink(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('setParameter')
            ->with('seoLink', 'php')
            ->willReturnSelf();

        $repo->queryAllBlogsByTag('php');
    }

    public function testQueryAllBlogsByTagAddsWhereCondition(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::once())
            ->method('where')
            ->with('t.seoLink = :seoLink')
            ->willReturnSelf();

        $repo->queryAllBlogsByTag('symfony');
    }

    public function testQueryAllBlogsByTagJoinsTagsAndBlogTags(): void
    {
        [$repo, $mockQb] = $this->makeRepo();
        $mockQb->expects(self::atLeast(2))
            ->method('innerJoin')
            ->willReturnSelf();

        $repo->queryAllBlogsByTag('php');
    }

    public function testQueryAllBlogsByTagReturnsDoctrineQuery(): void
    {
        [$repo] = $this->makeRepo();

        $result = $repo->queryAllBlogsByTag('php');

        self::assertInstanceOf(AbstractQuery::class, $result);
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Returns [repository-partial-mock, queryBuilder-mock].
     *
     * @return array{BlogRepository, MockObject&QueryBuilder}
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

        /** @var MockObject&BlogRepository $repo */
        $repo = $this->getMockBuilder(BlogRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        $repo->method('createQueryBuilder')->willReturn($mockQb);

        return [$repo, $mockQb];
    }
}
