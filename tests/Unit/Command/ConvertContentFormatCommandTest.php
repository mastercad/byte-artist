<?php

namespace App\Tests\Unit\Command;

use App\Command\ConvertContentFormatCommand;
use App\Entity\Blogs;
use App\Entity\Projects;
use App\Tests\Unit\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConvertContentFormatCommandTest extends BaseTestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    /** @var EntityRepository&MockObject */
    private EntityRepository $repository;

    private ConvertContentFormatCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->command = new ConvertContentFormatCommand($this->entityManager);
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($application->find('app:convert-content-format'));
    }

    // ------------------------------------------------------------------
    // execute() – table argument validation
    // ------------------------------------------------------------------

    public function testUnknownTableReturnsFailure(): void
    {
        $result = $this->commandTester->execute(['table' => 'unknown_table']);

        self::assertSame(Command::FAILURE, $result);
        self::assertStringContainsString('Unknown table "unknown_table"', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – empty entity list (both tables)
    // ------------------------------------------------------------------

    public function testEmptyEntitiesListBlogsReturnsSuccess(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testEmptyEntitiesListProjectsReturnsSuccess(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – null / empty content skip (blogs)
    // ------------------------------------------------------------------

    public function testNullContentIsSkippedForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(1);
        $blog->method('getContent')->willReturn(null);
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testEmptyContentIsSkippedForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(2);
        $blog->method('getContent')->willReturn('');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – null / empty description skip (projects)
    // ------------------------------------------------------------------

    public function testNullDescriptionIsSkippedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(3);
        $project->method('getDescription')->willReturn(null);
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testEmptyDescriptionIsSkippedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(4);
        $project->method('getDescription')->willReturn('');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – no UBB tags, no --force => skip (blogs + projects)
    // ------------------------------------------------------------------

    public function testPlainHtmlIsSkippedForBlogsWithoutForce(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(5);
        $blog->method('getContent')->willReturn('<p>Already plain HTML</p>');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testPlainHtmlIsSkippedForProjectsWithoutForce(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(6);
        $project->method('getDescription')->willReturn('<p>Already HTML</p>');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – noop: Replace produces no change (blogs + projects)
    // ------------------------------------------------------------------

    public function testNoopIsSkippedForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(7);
        $blog->method('getContent')->willReturn('[UNKNOWNTAG]some text[/UNKNOWNTAG]');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testNoopIsSkippedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(8);
        $project->method('getDescription')->willReturn('[UNKNOWNTAG]some text[/UNKNOWNTAG]');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – --force bypasses UBB detection, plain text → noop
    // ------------------------------------------------------------------

    public function testForceWithPlainTextIsNoopForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(9);
        $blog->method('getContent')->willReturn('plain text without any tags');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testForceWithPlainTextIsNoopForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(10);
        $project->method('getDescription')->willReturn('plain text without any tags');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – successful conversion, persisted (blogs + projects)
    // ------------------------------------------------------------------

    public function testUbbContentIsConvertedAndPersistedForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(11);
        $blog->method('getContent')->willReturn('[B]bold text[/B]');
        $blog->expects(self::once())->method('setContent')->willReturnSelf();
        $blog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::once())->method('persist')->with($blog);
        $this->entityManager->expects(self::once())->method('flush');
        $this->entityManager->expects(self::once())->method('clear');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testUbbContentIsConvertedAndPersistedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(12);
        $project->method('getDescription')->willReturn('[B]project content[/B]');
        $project->expects(self::once())->method('setDescription')->willReturnSelf();
        $project->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::once())->method('persist')->with($project);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testConvertedHtmlHasNoUbbTagsForBlogs(): void
    {
        $capturedHtml = null;

        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(13);
        $blog->method('getContent')->willReturn('[B]hello[/B]');
        $blog->expects(self::once())
            ->method('setContent')
            ->with(self::callback(static function (string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->commandTester->execute(['table' => 'blogs']);

        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('hello', $capturedHtml);
        self::assertStringNotContainsString('[B]', $capturedHtml);
    }

    public function testConvertedHtmlHasNoUbbTagsForProjects(): void
    {
        $capturedHtml = null;

        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(14);
        $project->method('getDescription')->willReturn('[B]world[/B]');
        $project->expects(self::once())
            ->method('setDescription')
            ->with(self::callback(static function (?string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();

        $this->repository->method('findAll')->willReturn([$project]);
        $this->commandTester->execute(['table' => 'projects']);

        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('world', $capturedHtml);
        self::assertStringNotContainsString('[B]', $capturedHtml);
    }

    // ------------------------------------------------------------------
    // execute() – --force converts UBB content for both tables
    // ------------------------------------------------------------------

    public function testForceConvertsUbbContentForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(15);
        $blog->method('getContent')->willReturn('[B]forced[/B]');
        $blog->expects(self::once())->method('setContent')->willReturnSelf();
        $blog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testForceConvertsUbbContentForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(16);
        $project->method('getDescription')->willReturn('[B]forced[/B]');
        $project->expects(self::once())->method('setDescription')->willReturnSelf();
        $project->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – dry-run suppresses all DB writes (blogs + projects)
    // ------------------------------------------------------------------

    public function testDryRunPreventsDbWritesForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(17);
        $blog->method('getContent')->willReturn('[B]bold text[/B]');
        $blog->expects(self::never())->method('setContent');
        $blog->expects(self::never())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $result = $this->commandTester->execute(['table' => 'blogs', '--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
        self::assertStringContainsString('DRY-RUN', $this->commandTester->getDisplay());
    }

    public function testDryRunPreventsDbWritesForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(18);
        $project->method('getDescription')->willReturn('[B]bold text[/B]');
        $project->expects(self::never())->method('setDescription');
        $project->expects(self::never())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $result = $this->commandTester->execute(['table' => 'projects', '--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
        self::assertStringContainsString('DRY-RUN', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – multiple entities, mixed skip + convert
    // ------------------------------------------------------------------

    public function testMultipleEntitiesWithMixedContentForBlogs(): void
    {
        $nullBlog = $this->createMock(Blogs::class);
        $nullBlog->method('getId')->willReturn(19);
        $nullBlog->method('getContent')->willReturn(null);

        $ubbBlog = $this->createMock(Blogs::class);
        $ubbBlog->method('getId')->willReturn(20);
        $ubbBlog->method('getContent')->willReturn('[B]to convert[/B]');
        $ubbBlog->expects(self::once())->method('setContent')->willReturnSelf();
        $ubbBlog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$nullBlog, $ubbBlog]);
        $this->entityManager->expects(self::once())->method('persist')->with($ubbBlog);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testMultipleEntitiesWithMixedContentForProjects(): void
    {
        $emptyProject = $this->createMock(Projects::class);
        $emptyProject->method('getId')->willReturn(21);
        $emptyProject->method('getDescription')->willReturn('');

        $ubbProject = $this->createMock(Projects::class);
        $ubbProject->method('getId')->willReturn(22);
        $ubbProject->method('getDescription')->willReturn('[B]to convert[/B]');
        $ubbProject->expects(self::once())->method('setDescription')->willReturnSelf();
        $ubbProject->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$emptyProject, $ubbProject]);
        $this->entityManager->expects(self::once())->method('persist')->with($ubbProject);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // execute() – verbose output for both skip reasons
    // ------------------------------------------------------------------

    public function testVerboseOutputShowsNoUbbTagsMessage(): void
    {
        // Plain text with no UBB tags and no newlines → "no UBB tags or newlines detected"
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(23);
        $blog->method('getContent')->willReturn('plain text without any tags or newlines');

        $this->repository->method('findAll')->willReturn([$blog]);

        $this->commandTester->execute(
            ['table' => 'blogs'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        self::assertStringContainsString('no UBB tags or newlines detected', $this->commandTester->getDisplay());
    }

    public function testVerboseOutputShowsNoopMessage(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(24);
        $blog->method('getContent')->willReturn('[UNKNOWNTAG]noop[/UNKNOWNTAG]');

        $this->repository->method('findAll')->willReturn([$blog]);

        $this->commandTester->execute(
            ['table' => 'blogs'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        self::assertStringContainsString('content unchanged after conversion', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // getContent() – all three match arms via reflection
    // ------------------------------------------------------------------

    public function testGetContentForBlogsReturnsEntityContent(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getContent')->willReturn('blog content');

        $result = self::callMethod($this->command, 'getContent', [$blog, 'blogs']);

        self::assertSame('blog content', $result);
    }

    public function testGetContentForProjectsReturnsEntityDescription(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getDescription')->willReturn('project description');

        $result = self::callMethod($this->command, 'getContent', [$project, 'projects']);

        self::assertSame('project description', $result);
    }

    public function testGetContentDefaultArmReturnsNull(): void
    {
        $result = self::callMethod($this->command, 'getContent', [new \stdClass(), 'unknown_table']);

        self::assertNull($result);
    }

    // ------------------------------------------------------------------
    // setContent() – all three match arms via reflection
    // ------------------------------------------------------------------

    public function testSetContentForBlogsCallsSetContent(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->expects(self::once())->method('setContent')->with('converted html')->willReturnSelf();

        self::callMethod($this->command, 'setContent', [$blog, 'blogs', 'converted html']);
    }

    public function testSetContentForProjectsCallsSetDescription(): void
    {
        $project = $this->createMock(Projects::class);
        $project->expects(self::once())->method('setDescription')->with('converted html')->willReturnSelf();

        self::callMethod($this->command, 'setContent', [$project, 'projects', 'converted html']);
    }

    public function testSetContentDefaultArmDoesNothing(): void
    {
        // Default arm returns null without calling any setter
        self::callMethod($this->command, 'setContent', [new \stdClass(), 'unknown_table', 'html']);

        self::assertTrue(true); // Reached without exception = default arm executed
    }

    // ------------------------------------------------------------------
    // NEW: mixed HTML + UBB content is NOT skipped – converted (main fix)
    // ------------------------------------------------------------------

    public function testMixedHtmlAndUbbIsConvertedForBlogs(): void
    {
        // Entry already has some <div> HTML from a prior partial conversion,
        // but still contains a [B] UBB tag → must NOT be skipped.
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(100);
        $blog->method('getContent')->willReturn('<div>existing html</div>[B]still ubb[/B]');
        $blog->expects(self::once())->method('setContent')->willReturnSelf();
        $blog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::once())->method('persist')->with($blog);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testMixedHtmlAndUbbIsConvertedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(101);
        $project->method('getDescription')->willReturn('<div style="float:left">existing</div>[url=http://example.com]link[/url]');
        $project->expects(self::once())->method('setDescription')->willReturnSelf();
        $project->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::once())->method('persist')->with($project);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testMixedHtmlAndUbbUbbTagsAreRemovedForBlogs(): void
    {
        $capturedHtml = null;

        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(102);
        $blog->method('getContent')->willReturn('<div>intro</div>[B]bold part[/B]');
        $blog->expects(self::once())
            ->method('setContent')
            ->with(self::callback(static function (string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->commandTester->execute(['table' => 'blogs']);

        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('bold part', $capturedHtml);
        self::assertStringNotContainsString('[B]', $capturedHtml);
    }

    public function testMixedHtmlAndUbbUbbTagsAreRemovedForProjects(): void
    {
        $capturedHtml = null;

        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(103);
        $project->method('getDescription')->willReturn('<div>intro</div>[url=http://example.com]my link[/url]');
        $project->expects(self::once())
            ->method('setDescription')
            ->with(self::callback(static function (?string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();

        $this->repository->method('findAll')->willReturn([$project]);
        $this->commandTester->execute(['table' => 'projects']);

        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('my link', $capturedHtml);
        self::assertStringNotContainsString('[url=', $capturedHtml);
    }

    // ------------------------------------------------------------------
    // NEW: plain text with newlines → nl2br fallback conversion
    // ------------------------------------------------------------------

    public function testPlainTextWithNewlinesIsConvertedViaNl2brForBlogs(): void
    {
        $capturedHtml = null;

        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(110);
        $blog->method('getContent')->willReturn("line one\nline two");
        $blog->expects(self::once())
            ->method('setContent')
            ->with(self::callback(static function (string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();
        $blog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('<br', $capturedHtml);
    }

    public function testPlainTextWithNewlinesIsConvertedViaNl2brForProjects(): void
    {
        $capturedHtml = null;

        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(111);
        $project->method('getDescription')->willReturn("first line\r\nsecond line");
        $project->expects(self::once())
            ->method('setDescription')
            ->with(self::callback(static function (?string $html) use (&$capturedHtml): bool {
                $capturedHtml = $html;

                return true;
            }))
            ->willReturnSelf();
        $project->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
        self::assertNotNull($capturedHtml);
        self::assertStringContainsString('<br', $capturedHtml);
    }

    // ------------------------------------------------------------------
    // NEW: plain text without newlines → skipped ("no UBB tags or newlines")
    // ------------------------------------------------------------------

    public function testPlainTextWithoutNewlinesIsSkippedForBlogs(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(120);
        $blog->method('getContent')->willReturn('just a single line of plain text');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testPlainTextWithoutNewlinesIsSkippedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(121);
        $project->method('getDescription')->willReturn('just a single line of plain text');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects']);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // NEW: verbose messages for the two new skip guards
    // ------------------------------------------------------------------

    public function testVerboseOutputShowsAlreadyHtmlNoUbbRemainingMessage(): void
    {
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(130);
        $blog->method('getContent')->willReturn('<p>fully converted html, no UBB left</p>');

        $this->repository->method('findAll')->willReturn([$blog]);

        $this->commandTester->execute(
            ['table' => 'blogs'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        self::assertStringContainsString('already contains HTML, no UBB remaining', $this->commandTester->getDisplay());
    }

    public function testVerboseOutputShowsNoUbbTagsOrNewlinesMessage(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(131);
        $project->method('getDescription')->willReturn('single line no tags');

        $this->repository->method('findAll')->willReturn([$project]);

        $this->commandTester->execute(
            ['table' => 'projects'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        self::assertStringContainsString('no UBB tags or newlines detected', $this->commandTester->getDisplay());
    }

    // ------------------------------------------------------------------
    // NEW: --force bypasses the "already HTML, no UBB" guard
    // ------------------------------------------------------------------

    public function testForceBypassesAlreadyHtmlGuardForBlogs(): void
    {
        // With --force, the HTML-already guard is skipped.
        // Content has HTML but no UBB and no newlines → filter produces no change → noop.
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(140);
        $blog->method('getContent')->willReturn('<p>Already HTML</p>');
        $blog->expects(self::never())->method('setContent');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        // Noop because the filter returns the content unchanged (no UBB to replace, no newlines).
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testForceBypassesAlreadyHtmlGuardForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(141);
        $project->method('getDescription')->willReturn('<p>Already HTML</p>');
        $project->expects(self::never())->method('setDescription');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::never())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('0 converted, 1 skipped', $this->commandTester->getDisplay());
    }

    public function testForceMixedHtmlAndUbbIsConvertedForBlogs(): void
    {
        // With --force, mixed HTML + UBB is converted (same as without force).
        $blog = $this->createMock(Blogs::class);
        $blog->method('getId')->willReturn(142);
        $blog->method('getContent')->willReturn('<div>existing</div>[B]still ubb[/B]');
        $blog->expects(self::once())->method('setContent')->willReturnSelf();
        $blog->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$blog]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'blogs', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }

    public function testForceMixedHtmlAndUbbIsConvertedForProjects(): void
    {
        $project = $this->createMock(Projects::class);
        $project->method('getId')->willReturn(143);
        $project->method('getDescription')->willReturn('<div>existing</div>[url=http://example.com]link[/url]');
        $project->expects(self::once())->method('setDescription')->willReturnSelf();
        $project->expects(self::once())->method('setModified');

        $this->repository->method('findAll')->willReturn([$project]);
        $this->entityManager->expects(self::once())->method('persist');

        $result = $this->commandTester->execute(['table' => 'projects', '--force' => true]);

        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('1 converted, 0 skipped', $this->commandTester->getDisplay());
    }
}
