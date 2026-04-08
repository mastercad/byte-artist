<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\Pagination;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginationTest extends TestCase
{
    private Pagination $pagination;

    protected function setUp(): void
    {
        $this->pagination = new Pagination();
    }

    // ------------------------------------------------------------------ lastPage

    public function testLastPageWithExactDivision(): void
    {
        $paginator = $this->makePaginator(count: 20, maxResults: 10);

        self::assertSame(2.0, $this->pagination->lastPage($paginator));
    }

    public function testLastPageCeilsPartialPage(): void
    {
        $paginator = $this->makePaginator(count: 25, maxResults: 10);

        self::assertSame(3.0, $this->pagination->lastPage($paginator));
    }

    public function testLastPageWithOneItem(): void
    {
        $paginator = $this->makePaginator(count: 1, maxResults: 10);

        self::assertSame(1.0, $this->pagination->lastPage($paginator));
    }

    public function testLastPageWithZeroItems(): void
    {
        $paginator = $this->makePaginator(count: 0, maxResults: 10);

        self::assertSame(0.0, $this->pagination->lastPage($paginator));
    }

    public function testLastPageWithCountEqualToMaxResults(): void
    {
        $paginator = $this->makePaginator(count: 10, maxResults: 10);

        self::assertSame(1.0, $this->pagination->lastPage($paginator));
    }

    public function testLastPageWithSingleItemPerPage(): void
    {
        $paginator = $this->makePaginator(count: 3, maxResults: 1);

        self::assertSame(3.0, $this->pagination->lastPage($paginator));
    }

    // ------------------------------------------------------------------ total

    public function testTotalDelegatesToPaginatorCount(): void
    {
        $paginator = $this->makePaginator(count: 42, maxResults: 10);

        self::assertSame(42, $this->pagination->total($paginator));
    }

    public function testTotalReturnsZeroWhenEmpty(): void
    {
        $paginator = $this->makePaginator(count: 0, maxResults: 10);

        self::assertSame(0, $this->pagination->total($paginator));
    }

    // ------------------------------------------------------------------ currentPageHasNoResult

    public function testCurrentPageHasNoResultReturnsTrueWhenIteratorIsEmpty(): void
    {
        /** @var MockObject&Paginator<object> $paginator */
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));

        self::assertTrue($this->pagination->currentPageHasNoResult($paginator));
    }

    public function testCurrentPageHasNoResultReturnsFalseWhenIteratorHasItems(): void
    {
        /** @var MockObject&Paginator<object> $paginator */
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator(['item-1', 'item-2']));

        self::assertFalse($this->pagination->currentPageHasNoResult($paginator));
    }

    public function testCurrentPageHasNoResultReturnsFalseForSingleItem(): void
    {
        /** @var MockObject&Paginator<object> $paginator */
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator(['only-item']));

        self::assertFalse($this->pagination->currentPageHasNoResult($paginator));
    }

    // ------------------------------------------------------------------ paginate
    // Note: Paginator::__construct clones the passed Query, so the paginator
    // works on the clone.  We use a real Query stub (disableOriginalConstructor,
    // no methods mocked) so setFirstResult/setMaxResults store real state that
    // we can assert on via $result->getQuery()->getFirstResult().

    private function makeQueryStub(): Query
    {
        /** @var Query $q */
        $q = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        return $q;
    }

    public function testPaginateReturnsDoctrinesPaginatorInstance(): void
    {
        $request = new Request(['p' => '1']);
        $result = $this->pagination->paginate($this->makeQueryStub(), $request, 10);

        self::assertInstanceOf(Paginator::class, $result);
    }

    public function testPaginateDefaultsToPageOneWhenParamAbsent(): void
    {
        // missing 'p' → getInt returns 0 → 0 ?: 1 = 1 → firstResult = limit*(1-1) = 0
        $request = new Request();
        $result = $this->pagination->paginate($this->makeQueryStub(), $request, 10);

        self::assertSame(0, $result->getQuery()->getFirstResult());
        self::assertSame(10, $result->getQuery()->getMaxResults());
    }

    public function testPaginateWithExplicitPageZeroDefaultsToPageOne(): void
    {
        // p=0 → 0 ?: 1 = 1 → firstResult = 0
        $request = new Request(['p' => '0']);
        $result = $this->pagination->paginate($this->makeQueryStub(), $request, 10);

        self::assertSame(0, $result->getQuery()->getFirstResult());
    }

    public function testPaginateCalculatesCorrectFirstResultForPageThree(): void
    {
        // p=3 → firstResult = 10 * (3-1) = 20
        $request = new Request(['p' => '3']);
        $result = $this->pagination->paginate($this->makeQueryStub(), $request, 10);

        self::assertSame(20, $result->getQuery()->getFirstResult());
    }

    public function testPaginateSetsMaxResultsFromLimit(): void
    {
        $request = new Request(['p' => '1']);
        $result = $this->pagination->paginate($this->makeQueryStub(), $request, 5);

        self::assertSame(5, $result->getQuery()->getMaxResults());
    }

    // ------------------------------------------------------------------ helpers

    /**
     * @return MockObject&Paginator<object>
     */
    private function makePaginator(int $count, int $maxResults): MockObject
    {
        /** @var MockObject&Query $mockQuery */
        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMaxResults'])
            ->getMock();
        $mockQuery->method('getMaxResults')->willReturn($maxResults);

        /** @var MockObject&Paginator<object> $paginator */
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('count')->willReturn($count);
        $paginator->method('getQuery')->willReturn($mockQuery);

        return $paginator;
    }
}
