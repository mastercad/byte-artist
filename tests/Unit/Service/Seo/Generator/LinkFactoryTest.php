<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Seo\Generator;

use App\Entity\Projects;
use App\Repository\ProjectsRepository;
use App\Service\Seo\Generator\Link;
use App\Service\Seo\Generator\LinkFactory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkFactoryTest extends TestCase
{
    // ------------------------------------------------------------------ create: returns Link instance

    public function testCreateReturnsLinkInstance(): void
    {
        /** @var MockObject&ObjectRepository<object> $repo */
        $repo = $this->createMock(ObjectRepository::class);
        /** @var MockObject&ManagerRegistry $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getRepository')->willReturn($repo);

        $factory = new LinkFactory($registry);
        $link = $factory->create(Projects::class, 'name');

        self::assertInstanceOf(Link::class, $link);
    }

    // ------------------------------------------------------------------ create: fetches correct repository

    public function testCreateFetchesRepositoryForGivenEntityClass(): void
    {
        /** @var MockObject&ObjectRepository<object> $repo */
        $repo = $this->createMock(ObjectRepository::class);
        /** @var MockObject&ManagerRegistry $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())
            ->method('getRepository')
            ->with(Projects::class)
            ->willReturn($repo);

        $factory = new LinkFactory($registry);
        $factory->create(Projects::class, 'name');
    }

    // ------------------------------------------------------------------ create: produced Link uses correct column

    public function testCreatedLinkUsesCorrectColumnForSeoLinkGeneration(): void
    {
        /** @var MockObject&ProjectsRepository $repo */
        $repo = $this->createMock(ProjectsRepository::class);
        // The produced Link will call findOneBy on the repo during seoLink generation.
        $repo->method('findOneBy')->willReturn(null);

        /** @var MockObject&ManagerRegistry $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getRepository')->willReturn($repo);

        $factory = new LinkFactory($registry);
        $link = $factory->create(Projects::class, 'name');

        // Use the Link: it should call getName() on the entity to derive the seo link
        $project = new Projects();
        $project->setName('My Project');
        $link->extendWithSeoLink($project);

        self::assertSame('my-project', $project->getSeoLink());
    }

    // ------------------------------------------------------------------ create: different column names

    public function testCreateWithDifferentColumnNameProducesLinkForThatColumn(): void
    {
        /** @var MockObject&ObjectRepository<object> $repo */
        $repo = $this->createMock(ObjectRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        /** @var MockObject&ManagerRegistry $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getRepository')->willReturn($repo);

        $factory = new LinkFactory($registry);
        // 'shortDescription' column → Link calls getShortDescription()
        // We use a Projects which has getShortDescription()
        $link = $factory->create(Projects::class, 'shortDescription');

        $project = new Projects();
        $project->setShortDescription('Hello World');
        $link->extendWithSeoLink($project);

        self::assertSame('hello-world', $project->getSeoLink());
    }
}
