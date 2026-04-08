<?php

namespace App\Tests\Unit\Tests\Service\Seo\Generator;

use App\Entity\Projects;
use App\Repository\ProjectsRepository;
use App\Service\Seo\Generator\Link;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function testExtendWithSeoLinkWithoutAnyDbData(): void
    {
        /** @var MockObject&ProjectsRepository */
        $repositoryMock = $this->createMock(ProjectsRepository::class);

        $projectEntity = new Projects();
        $projectEntity->setName('Attention: This is a testname for the test without data in database');

        $repositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database'])
            ->willReturn(null);

        $linkService = new Link($repositoryMock, 'name');
        $linkService->extendWithSeoLink($projectEntity);

        self::assertEquals(
            'attention-this-is-a-testname-for-the-test-without-data-in-database',
            $projectEntity->getSeoLink()
        );
    }

    public function testExtendWithSeoLinkWitOneMatchingEntryInDbData(): void
    {
        /** @var MockObject&ProjectsRepository */
        $repositoryMock = $this->createMock(ProjectsRepository::class);

        $projectEntity = new Projects();
        $projectEntity->setId(10)
            ->setName('Attention: This is a testname for the test without data in database');

        $dbEntityOne = new Projects();
        $dbEntityOne->setId(1)
            ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

        // Call 1: base slug exists (different entity) → recurse to '-1'
        // Call 2: '-1' slug is free → stop
        $returnMap = [
            'attention-this-is-a-testname-for-the-test-without-data-in-database'   => $dbEntityOne,
            'attention-this-is-a-testname-for-the-test-without-data-in-database-1' => null,
        ];
        $repositoryMock->method('findOneBy')
            ->willReturnCallback(static function (array $criteria) use ($returnMap) {
                return $returnMap[$criteria['seoLink']];
            });

        $linkService = new Link($repositoryMock, 'name');
        $linkService->extendWithSeoLink($projectEntity);

        self::assertEquals(
            'attention-this-is-a-testname-for-the-test-without-data-in-database-1',
            $projectEntity->getSeoLink()
        );
    }

    public function testExtendWithSeoLinkWitOneMatchingEntryAndSelfInDbData(): void
    {
        /** @var MockObject&ProjectsRepository */
        $repositoryMock = $this->createMock(ProjectsRepository::class);

        $projectEntity = new Projects();
        $projectEntity->setId(10)
            ->setName('Attention: This is a testname for the test without data in database');

        $dbEntityOne = new Projects();
        $dbEntityOne->setId(1)
            ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

        // dbEntityTwo has the same id as $projectEntity → it IS the entity being saved → treat as free
        $dbEntityTwo = new Projects();
        $dbEntityTwo->setId(10)
            ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database-1');

        // Call 1: base slug taken by id=1 (different) → recurse to '-1'
        // Call 2: '-1' slug taken by id=10 (self) → self-match, accept '-1'
        $returnMap = [
            'attention-this-is-a-testname-for-the-test-without-data-in-database'   => $dbEntityOne,
            'attention-this-is-a-testname-for-the-test-without-data-in-database-1' => $dbEntityTwo,
        ];
        $repositoryMock->method('findOneBy')
            ->willReturnCallback(static function (array $criteria) use ($returnMap) {
                return $returnMap[$criteria['seoLink']];
            });

        $linkService = new Link($repositoryMock, 'name');
        $linkService->extendWithSeoLink($projectEntity);

        self::assertEquals(
            'attention-this-is-a-testname-for-the-test-without-data-in-database-1',
            $projectEntity->getSeoLink()
        );
    }

    public function testExtendWithSeoLinkWitThreeMatchingEntryInDbData(): void
    {
        /** @var MockObject&ProjectsRepository */
        $repositoryMock = $this->createMock(ProjectsRepository::class);

        $projectEntity = new Projects();
        $projectEntity->setId(10)
            ->setName('Attention: This is a testname for the test without data in database');

        $dbEntityOne = (new Projects())->setId(1);
        $dbEntityTwo = (new Projects())->setId(2);
        $dbEntityThree = (new Projects())->setId(3);

        // Call 1: base taken → '-1'; Call 2: '-1' taken → '-2'; Call 3: '-2' taken → '-3'; Call 4: '-3' free
        $returnMap = [
            'attention-this-is-a-testname-for-the-test-without-data-in-database'   => $dbEntityOne,
            'attention-this-is-a-testname-for-the-test-without-data-in-database-1' => $dbEntityTwo,
            'attention-this-is-a-testname-for-the-test-without-data-in-database-2' => $dbEntityThree,
            'attention-this-is-a-testname-for-the-test-without-data-in-database-3' => null,
        ];
        $repositoryMock->method('findOneBy')
            ->willReturnCallback(static function (array $criteria) use ($returnMap) {
                return $returnMap[$criteria['seoLink']];
            });

        $linkService = new Link($repositoryMock, 'name');
        $linkService->extendWithSeoLink($projectEntity);

        self::assertEquals(
            'attention-this-is-a-testname-for-the-test-without-data-in-database-3',
            $projectEntity->getSeoLink()
        );
    }
}

  public function testExtendWithSeoLinkWithoutAnyDbData()
  {
    /** @var MockObject|EntityRepository */
    $reportRepositoryMock = $this->createMock(ProjectsRepository::class);
    $projectEntity = new Projects();
    $projectEntity->setName('Attention: This is a testname for the test without data in database');

    $reportRepositoryMock->expects(self::once())
      ->method('findOneBy')
      ->withConsecutive([['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database']])
      ->willReturnOnConsecutiveCalls(false);

    $linkService = new Link($reportRepositoryMock, 'name');
    $linkService->extendWithSeoLink($projectEntity);

    self::assertEquals(
      'attention-this-is-a-testname-for-the-test-without-data-in-database',
      $projectEntity->getSeoLink()
    );
  }

  public function testExtendWithSeoLinkWitOneMatchingEntryInDbData()
  {
    /** @var MockObject|EntityRepository */
    $reportRepositoryMock = $this->createMock(ProjectsRepository::class);

    $projectEntity = new Projects();
    $dbEntityOne = new Projects();
    $dbEntityOne->setName('Attention: This is a testname for the test without data in database')
      ->setId(1)
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

    $projectEntity->setId(10)
      ->setName('Attention: This is a testname for the test without data in database');

      $reportRepositoryMock->expects($this->at(0))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database'])
      ->willReturn($dbEntityOne);

      $reportRepositoryMock->expects($this->at(1))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database-1'])
      ->willReturn(false);

    $linkService = new Link($reportRepositoryMock, 'name');
    $linkService->extendWithSeoLink($projectEntity);

    self::assertEquals(
      'attention-this-is-a-testname-for-the-test-without-data-in-database-1',
      $projectEntity->getSeoLink()
    );
  }

  public function testExtendWithSeoLinkWitOneMatchingEntryAndSelfInDbData()
  {
    /** @var MockObject|EntityRepository */
    $reportRepositoryMock = $this->createMock(ProjectsRepository::class);

    $projectEntity = new Projects();
    $projectEntity->setId(10)
      ->setName('Attention: This is a testname for the test without data in database');

    $dbEntityOne = new Projects();
    $dbEntityOne->setId(1)
      ->setName('Attention: This is a testname for the test without data in database')
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

    $dbEntityTwo = new Projects();
    $dbEntityTwo->setId(10)
      ->setName('Attention: This is a testname for the test without data in database')
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

    $reportRepositoryMock->expects($this->at(0))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database'])
      ->willReturn($dbEntityOne);

    $reportRepositoryMock->expects($this->at(1))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database-1'])
      ->willReturn($dbEntityTwo);

    $linkService = new Link($reportRepositoryMock, 'name');
    $linkService->extendWithSeoLink($projectEntity);

    self::assertEquals(
      'attention-this-is-a-testname-for-the-test-without-data-in-database-1',
      $projectEntity->getSeoLink()
    );
  }

  public function testExtendWithSeoLinkWitThreeMatchingEntryInDbData()
  {
    /** @var MockObject|EntityRepository */
    $reportRepositoryMock = $this->createMock(ProjectsRepository::class);

    $projectEntity = new Projects();
    $projectEntity->setId(10)
      ->setName('Attention: This is a testname for the test without data in database');

    $dbEntityOne = new Projects();
    $dbEntityOne->setId(1)
      ->setName('Attention: This is a testname for the test without data in database')
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database');

    $dbEntityTwo = new Projects();
    $dbEntityTwo->setId(2)
      ->setName('Attention: This is a testname for the test without data in database')
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database-1');

    $dbEntityThree = new Projects();
    $dbEntityThree->setId(3)
      ->setName('Attention: This is a testname for the test without data in database')
      ->setSeoLink('attention-this-is-a-testname-for-the-test-without-data-in-database-2');

    $reportRepositoryMock->expects($this->at(0))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database'])
      ->willReturn($dbEntityOne);

    $reportRepositoryMock->expects($this->at(1))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database-1'])
      ->willReturn($dbEntityTwo);

    $reportRepositoryMock->expects($this->at(2))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database-2'])
      ->willReturn($dbEntityThree);

      $reportRepositoryMock->expects($this->at(3))
      ->method('findOneBy')
      ->with(['seoLink' => 'attention-this-is-a-testname-for-the-test-without-data-in-database-3'])
      ->willReturn([]);

    $linkService = new Link($reportRepositoryMock, 'name');
    $linkService->extendWithSeoLink($projectEntity);

    self::assertEquals(
      'attention-this-is-a-testname-for-the-test-without-data-in-database-3',
      $projectEntity->getSeoLink()
    );
  }
}
