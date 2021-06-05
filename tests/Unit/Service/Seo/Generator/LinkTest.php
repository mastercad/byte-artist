<?php

namespace App\Tests\Unit\Tests\Service\Seo\Generator;

use App\Entity\Projects;
use App\Repository\ProjectsRepository;
use App\Service\Seo\Generator\Link;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
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
