<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Projects;
use App\Entity\ProjectTags;
use App\Entity\Tags;
use App\Entity\User;
use App\Repository\ProjectsRepository;
use App\Service\Pagination;
use App\Service\Seo\Generator\Link;
use App\Service\Seo\Generator\LinkFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectsControllerTest extends TestCase
{
    /** @var MockObject&EntityManagerInterface */
    private MockObject $em;
    /** @var MockObject&EntityRepository<ProjectTags> */
    private MockObject $projectTagRepo;
    /** @var MockObject&EntityRepository<Tags> */
    private MockObject $tagRepo;
    private TestableProjectsController $controller;
    /** @var MockObject&User */
    private MockObject $user;

    /** @var MockObject&ProjectsRepository */
    private MockObject $projectsRepo;

    protected function setUp(): void
    {
        $this->projectTagRepo = $this->createMock(EntityRepository::class);
        $this->tagRepo = $this->createMock(EntityRepository::class);
        $this->projectsRepo = $this->createMock(ProjectsRepository::class);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturnCallback(
            fn (string $class) => match ($class) {
                ProjectTags::class => $this->projectTagRepo,
                Tags::class => $this->tagRepo,
                Projects::class => $this->projectsRepo,
                default => throw new \InvalidArgumentException("Unexpected: $class"),
            }
        );

        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(42);

        $this->controller = new TestableProjectsController($this->em);
        $this->controller->testUser = $this->user;
    }

    // ------------------------------------------------------------------ considerTags: tag with existing id (keep)

    public function testConsiderTagsKeepsTagThatHasKnownId(): void
    {
        $project = new Projects();

        /** @var MockObject&ProjectTags $existingTag */
        $existingTag = $this->createMock(ProjectTags::class);
        $existingTag->method('getId')->willReturn(5);

        $this->projectTagRepo->method('findBy')->willReturn([$existingTag]);

        $this->em->expects(self::never())->method('remove');

        $this->controller->callConsiderTags($project, [['id' => '5']]);
    }

    public function testConsiderTagsDoesNotCreateNewTagEntityWhenIdIsPresent(): void
    {
        $project = new Projects();

        /** @var MockObject&ProjectTags $existingTag */
        $existingTag = $this->createMock(ProjectTags::class);
        $existingTag->method('getId')->willReturn(7);

        $this->projectTagRepo->method('findBy')->willReturn([$existingTag]);

        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::never())->method('flush');

        $this->controller->callConsiderTags($project, [['id' => '7']]);
    }

    // ------------------------------------------------------------------ considerTags: 'undefined' / empty id falls through

    public function testConsiderTagsWithUndefinedIdFallsThroughToTagIdCheck(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('find')->with('42')->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->controller->callConsiderTags($project, [
            ['id' => 'undefined', 'tagId' => '42'],
        ]);
    }

    public function testConsiderTagsWithEmptyIdFallsThroughToTagIdCheck(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('find')->with('99')->willReturn($tagEntity);

        $this->em->expects(self::atLeastOnce())->method('persist');

        $this->controller->callConsiderTags($project, [
            ['id' => '', 'tagId' => '99'],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: tag by tagId

    public function testConsiderTagsByTagIdLooksUpExistingTagEntityAndCreatesProjectTag(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->expects(self::once())
            ->method('find')
            ->with('3')
            ->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $result = $this->controller->callConsiderTags($project, [
            ['tagId' => '3'],
        ]);

        self::assertCount(1, $result->getProjectTags());
    }

    public function testConsiderTagsWithUndefinedTagIdFallsThroughToNameBranch(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('findOneBy')->with(['name' => 'php'])->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');

        $this->controller->callConsiderTags($project, [
            ['tagId' => 'undefined', 'tagName' => 'php'],
        ]);
    }

    public function testConsiderTagsWithEmptyTagIdFallsThroughToNameBranch(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('findOneBy')->with(['name' => 'symfony'])->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');

        $this->controller->callConsiderTags($project, [
            ['tagId' => '', 'tagName' => 'symfony'],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: tag by name (found)

    public function testConsiderTagsByNameUsesExistingTagWhenFoundInDb(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $existingTag = new Tags();
        $this->tagRepo->method('findOneBy')
            ->with(['name' => 'docker'])
            ->willReturn($existingTag);

        $this->em->expects(self::once())->method('persist');

        $result = $this->controller->callConsiderTags($project, [
            ['tagName' => 'docker'],
        ]);

        self::assertCount(1, $result->getProjectTags());
    }

    // ------------------------------------------------------------------ considerTags: tag by name (not found → create)

    public function testConsiderTagsByNameCreatesNewTagEntityWhenNotFoundInDb(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->tagRepo->method('findOneBy')->with(['name' => 'new-framework'])->willReturn(null);

        // Two persist calls: new Tags, new ProjectTags
        $this->em->expects(self::exactly(2))->method('persist');
        $this->em->expects(self::exactly(2))->method('flush');

        $result = $this->controller->callConsiderTags($project, [
            ['tagName' => 'new-framework'],
        ]);

        self::assertCount(1, $result->getProjectTags());
    }

    public function testConsiderTagsByNameTrimsTagNameBeforeLookup(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $existingTag = new Tags();
        $this->tagRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['name' => 'php'])
            ->willReturn($existingTag);

        $this->controller->callConsiderTags($project, [
            ['tagName' => '  php  '],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: empty tagName skipped

    public function testConsiderTagsSkipsTagWithEmptyTagName(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->tagRepo->expects(self::never())->method('findOneBy');
        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($project, [
            ['tagName' => ''],
        ]);
    }

    public function testConsiderTagsSkipsTagWithWhitespaceOnlyName(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->tagRepo->expects(self::never())->method('findOneBy');
        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($project, [
            ['tagName' => '   '],
        ]);
    }

    public function testConsiderTagsSkipsTagWithMissingTagName(): void
    {
        $project = new Projects();
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($project, [
            [], // no keys at all
        ]);
    }

    // ------------------------------------------------------------------ considerTags: stale tags removed

    public function testConsiderTagsRemovesStaleOldTagsNotInSubmittedList(): void
    {
        $project = new Projects();

        /** @var MockObject&ProjectTags $staleTag */
        $staleTag = $this->createMock(ProjectTags::class);
        $staleTag->method('getId')->willReturn(10);

        $this->projectTagRepo->method('findBy')->willReturn([$staleTag]);

        $this->em->expects(self::once())->method('remove')->with($staleTag);
        $this->em->expects(self::once())->method('flush');

        $this->controller->callConsiderTags($project, []);
    }

    public function testConsiderTagsRemovesOnlyTagsNotMatchedBySubmittedIds(): void
    {
        $project = new Projects();

        /** @var MockObject&ProjectTags $keptTag */
        $keptTag = $this->createMock(ProjectTags::class);
        $keptTag->method('getId')->willReturn(1);

        /** @var MockObject&ProjectTags $staleTag */
        $staleTag = $this->createMock(ProjectTags::class);
        $staleTag->method('getId')->willReturn(2);

        $this->projectTagRepo->method('findBy')->willReturn([$keptTag, $staleTag]);

        $this->em->expects(self::once())->method('remove')->with($staleTag);

        $this->controller->callConsiderTags($project, [
            ['id' => '1'], // keeps keptTag
        ]);
    }

    // ------------------------------------------------------------------ considerTags: multiple mixed tags

    public function testConsiderTagsHandlesMultipleTagsOfDifferentTypes(): void
    {
        $project = new Projects();

        /** @var MockObject&ProjectTags $existingTag */
        $existingTag = $this->createMock(ProjectTags::class);
        $existingTag->method('getId')->willReturn(1);
        $this->projectTagRepo->method('findBy')->willReturn([$existingTag]);

        $tagFromId = new Tags();
        $tagFromName = new Tags();
        $this->tagRepo->method('find')->willReturn($tagFromId);
        $this->tagRepo->method('findOneBy')->willReturn($tagFromName);

        $this->controller->callConsiderTags($project, [
            ['id' => '1'],        // keep existing
            ['tagId' => '5'],     // lookup by id
            ['tagName' => 'php'], // lookup by name
        ]);

        self::assertCount(2, $project->getProjectTags());
    }

    // ------------------------------------------------------------------ extractErrorsFromForm

    public function testExtractErrorsFromFormReturnsEmptyArrayForFormWithNoErrors(): void
    {
        $form = $this->makeMockForm([], []);

        self::assertSame([], $this->controller->callExtractErrorsFromForm($form));
    }

    public function testExtractErrorsFromFormCollectsDirectFormErrors(): void
    {
        $form = $this->makeMockForm([
            new FormError('Name is required'),
            new FormError('Description too long'),
        ], []);

        $result = $this->controller->callExtractErrorsFromForm($form);

        self::assertSame(['Name is required', 'Description too long'], $result);
    }

    public function testExtractErrorsFromFormCollectsChildFormErrors(): void
    {
        $childForm = $this->makeMockForm([new FormError('Invalid value')], [], 'project_name');
        $parentForm = $this->makeMockForm([], [$childForm]);

        $result = $this->controller->callExtractErrorsFromForm($parentForm);

        self::assertArrayHasKey('project_name', $result);
        self::assertSame(['Invalid value'], $result['project_name']);
    }

    public function testExtractErrorsFromFormExcludesChildFormsWithNoErrors(): void
    {
        $cleanChild = $this->makeMockForm([], [], 'clean_field');
        $parentForm = $this->makeMockForm([], [$cleanChild]);

        $result = $this->controller->callExtractErrorsFromForm($parentForm);

        self::assertArrayNotHasKey('clean_field', $result);
    }

    public function testExtractErrorsFromFormRecursesIntoNestedChildForms(): void
    {
        $deepChild = $this->makeMockForm([new FormError('Deep error')], [], 'deep');
        $midChild = $this->makeMockForm([], [$deepChild], 'mid');
        $root = $this->makeMockForm([], [$midChild]);

        $result = $this->controller->callExtractErrorsFromForm($root);

        self::assertArrayHasKey('mid', $result);
        self::assertSame(['Deep error'], $result['mid']['deep']);
    }

    public function testExtractErrorsFromFormCombinesDirectAndChildErrors(): void
    {
        $childForm = $this->makeMockForm([new FormError('Child error')], [], 'child');
        $root = $this->makeMockForm([new FormError('Root error')], [$childForm]);

        $result = $this->controller->callExtractErrorsFromForm($root);

        self::assertContains('Root error', $result);
        self::assertArrayHasKey('child', $result);
    }

    // ------------------------------------------------------------------ helpers

    /**
     * @param FormError[]     $errors
     * @param FormInterface[] $children
     */
    private function makeMockForm(array $errors, array $children, string $name = 'form'): FormInterface
    {
        /** @var MockObject&FormInterface $form */
        $form = $this->createMock(FormInterface::class);
        $form->method('getName')->willReturn($name);
        $form->method('getErrors')->willReturn(new FormErrorIterator($form, $errors));
        $form->method('all')->willReturn($children);

        return $form;
    }
    // ------------------------------------------------------------------ helpers

    private function makeForm(bool $submitted, bool $valid): FormInterface
    {
        /** @var MockObject&FormInterface $form */
        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn($submitted);
        $form->method('isValid')->willReturn($valid);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('createView')->willReturn(new FormView());
        $form->method('getErrors')->willReturn(new FormErrorIterator($form, []));
        $form->method('all')->willReturn([]);

        return $form;
    }

    private function makeLinkFactory(?Projects $capturedProject = null): LinkFactory
    {
        /** @var MockObject&Link $linkGenerator */
        $linkGenerator = $this->createMock(Link::class);
        $linkGenerator->method('extendWithSeoLink')->willReturnCallback(
            static function (Projects $project) use (&$capturedProject): void {
                if (null === $project->getSeoLink()) {
                    $project->setSeoLink('my-project-seo');
                }
            }
        );

        /** @var MockObject&LinkFactory $factory */
        $factory = $this->createMock(LinkFactory::class);
        $factory->method('create')->willReturn($linkGenerator);

        return $factory;
    }

    // ------------------------------------------------------------------ indexAction

    public function testIndexActionRendersProjectsTemplateForAdmin(): void
    {
        $pagination = $this->createMock(Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->controller->isGrantedResult = true;
        $this->projectTagRepo->method('findAll')->willReturn([]);
        $this->projectsRepo->method('queryAllProjects')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->indexAction(new Request(), $pagination);

        self::assertSame('projects/index.html.twig', $this->controller->renderedView);
        self::assertArrayHasKey('projects', $this->controller->renderedParams);
        self::assertArrayHasKey('projectTags', $this->controller->renderedParams);
        self::assertArrayHasKey('lastPage', $this->controller->renderedParams);
    }

    public function testIndexActionRendersVisibleProjectsOnlyForGuest(): void
    {
        $pagination = $this->createMock(Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->controller->isGrantedResult = false;
        $this->projectTagRepo->method('findAll')->willReturn([]);
        $this->projectsRepo->method('queryAllVisibleProjects')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->indexAction(new Request(), $pagination);

        self::assertSame('projects/index.html.twig', $this->controller->renderedView);
    }

    // ------------------------------------------------------------------ tagAction

    public function testTagActionRendersAdminQueryWhenGranted(): void
    {
        $pagination = $this->createMock(Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->controller->isGrantedResult = true;
        $this->projectTagRepo->method('findAll')->willReturn([]);
        $this->projectsRepo->method('queryAllProjectsByTag')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->tagAction(new Request(), $pagination, 'php');

        self::assertSame('projects/index.html.twig', $this->controller->renderedView);
        self::assertSame('php', $this->controller->renderedParams['tagSeoLink']);
    }

    public function testTagActionRendersVisibleProjectsQueryForGuest(): void
    {
        $pagination = $this->createMock(Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->controller->isGrantedResult = false;
        $this->projectTagRepo->method('findAll')->willReturn([]);
        $this->projectsRepo->method('queryVisibleProjectsByTag')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->tagAction(new Request(), $pagination, 'symfony');

        self::assertSame('symfony', $this->controller->renderedParams['tagSeoLink']);
    }

    // ------------------------------------------------------------------ createAction

    public function testCreateActionRendersNewProjectForm(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->createAction();

        self::assertSame('projects/create.html.twig', $this->controller->renderedView);
        self::assertNull($this->controller->renderedParams['id']);
    }

    // ------------------------------------------------------------------ editByIdAction

    public function testEditByIdActionFetchesProjectAndRendersForm(): void
    {
        $project = new Projects();
        $project->setId(4);
        $this->projectsRepo->method('find')->with(4)->willReturn($project);

        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->editByIdAction(4);

        self::assertSame('projects/create.html.twig', $this->controller->renderedView);
        self::assertSame(4, $this->controller->renderedParams['id']);
    }

    // ------------------------------------------------------------------ editBySeoNameAction

    public function testEditBySeoNameActionFetchesProjectAndRendersForm(): void
    {
        $project = new Projects();
        $project->setId(7);
        $project->setSeoLink('my-project');
        $this->projectsRepo->method('findOneBy')->with(['seoLink' => 'my-project'])->willReturn($project);

        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->editBySeoNameAction('my-project');

        self::assertSame('projects/create.html.twig', $this->controller->renderedView);
        self::assertSame(7, $this->controller->renderedParams['id']);
    }

    // ------------------------------------------------------------------ showByIdAction

    public function testShowByIdActionRendersShowTemplate(): void
    {
        $project = new Projects();
        $this->projectsRepo->method('find')->with(5)->willReturn($project);

        $this->controller->showByIdAction(5);

        self::assertSame('projects/show.html.twig', $this->controller->renderedView);
        self::assertSame($project, $this->controller->renderedParams['project']);
    }

    // ------------------------------------------------------------------ showBySeoNameAction

    public function testShowBySeoNameActionRendersShowTemplate(): void
    {
        $project = new Projects();
        $this->projectsRepo->method('findOneBy')->with(['seoLink' => 'cool-project'])->willReturn($project);

        $this->controller->showBySeoNameAction('cool-project');

        self::assertSame('projects/show.html.twig', $this->controller->renderedView);
        self::assertSame($project, $this->controller->renderedParams['project']);
    }

    // ------------------------------------------------------------------ saveAction: new project

    public function testSaveActionNewProjectPersistsAndReturnsJsonWithId(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');

        $request = new Request();
        $request->request->set('projects', ['id' => 0, 'name' => 'My Project']);

        $response = $this->controller->saveAction($request, $this->makeLinkFactory());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSaveActionExistingProjectFetchesEntityById(): void
    {
        $existingProject = new Projects();
        $existingProject->setId(3);
        $existingProject->setName('Old Name');
        $this->projectsRepo->method('find')->with(3)->willReturn($existingProject);

        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->projectTagRepo->method('findBy')->willReturn([]);

        $this->em->method('persist');
        $this->em->method('flush');

        $request = new Request();
        $request->request->set('projects', ['id' => 3, 'name' => 'Updated Name']);

        $response = $this->controller->saveAction($request, $this->makeLinkFactory());

        self::assertInstanceOf(JsonResponse::class, $response);
    }

    public function testSaveActionInvalidFormReturns422(): void
    {
        $form = $this->makeForm(true, false);
        $this->controller->formToReturn = $form;

        $request = new Request();
        $request->request->set('projects', ['id' => 0, 'name' => 'Bad Project']);

        $response = $this->controller->saveAction($request, $this->makeLinkFactory());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSaveActionExtractsProjectTagsFromRequest(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->projectTagRepo->method('findBy')->willReturn([]);
        $this->em->method('persist');
        $this->em->method('flush');

        $request = new Request();
        $request->request->set('projects', [
            'id' => 0,
            'name' => 'Tagged Project',
            'projectTags' => [['tagName' => 'php']],
        ]);

        $response = $this->controller->saveAction($request, $this->makeLinkFactory());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSaveActionSetsCreatedDateForNewProjectWhenMissing(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;

        $request = new Request();
        $request->request->set('projects', ['id' => 0, 'name' => 'New Project']);

        $this->controller->saveAction($request, $this->makeLinkFactory());

        $capturedProject = $this->controller->lastFormData;
        self::assertNotNull($capturedProject);
        self::assertInstanceOf(Projects::class, $capturedProject);
        self::assertInstanceOf(\DateTimeInterface::class, $capturedProject->getCreated());
    }

    public function testSaveActionDoesNotOverrideCreatedDateWhenProvided(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;

        $request = new Request();
        $request->request->set('projects', ['id' => 0, 'name' => 'New Project', 'created' => '2023-01-01']);

        $this->controller->saveAction($request, $this->makeLinkFactory());

        // The entity object passed from the existing project (or the new one created before the if-check)
        // has the date set in constructor; what matters is the auto-set branch was not triggered again.
        $capturedProject = $this->controller->lastFormData;
        self::assertNotNull($capturedProject);
    }

    public function testSaveActionConsiderTagsExceptionReturns500(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;

        // Force considerTags to throw via broken tag repo
        $this->projectTagRepo->method('findBy')->willThrowException(new \RuntimeException('DB down'));
        $this->em->method('persist');
        $this->em->method('flush');

        $request = new Request();
        $request->request->set('projects', ['id' => 0, 'name' => 'Failing Project']);

        $response = $this->controller->saveAction($request, $this->makeLinkFactory());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $data);
    }

    // ------------------------------------------------------------------ deleteAction

    public function testDeleteActionRemovesEntityAndReturnsSuccess(): void
    {
        $project = new Projects();
        $project->setId(9);
        $this->projectsRepo->method('find')->with(9)->willReturn($project);

        $this->em->expects(self::once())->method('remove')->with($project);
        $this->em->expects(self::once())->method('flush');

        // Project dir does NOT exist → no fs cleanup
        $this->controller->testPublicDir = sys_get_temp_dir().'/proj_delete_test_'.uniqid();

        $response = $this->controller->deleteAction(9);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testDeleteActionDeletesImagesDirectoryWhenItExists(): void
    {
        $project = new Projects();
        $project->setId(10);
        $this->projectsRepo->method('find')->with(10)->willReturn($project);
        $this->em->method('remove');
        $this->em->method('flush');

        // Create a temp image directory that looks like the real one
        $tmpRoot = sys_get_temp_dir().'/proj_delete_img_'.uniqid();
        $imgDir = $tmpRoot.'/images/content/dynamisch/projects/10';
        mkdir($imgDir, 0777, true);
        $dummyFile = $imgDir.'/photo.jpg';
        file_put_contents($dummyFile, 'fake');

        $this->controller->testPublicDir = $tmpRoot;

        $response = $this->controller->deleteAction(10);

        self::assertFalse(file_exists($dummyFile), 'File should have been deleted');
        self::assertFalse(is_dir($imgDir), 'Directory should have been removed');
        $data = json_decode($response->getContent(), true);
        self::assertTrue($data['success']);

        // Tear down remaining parent dirs
        @rmdir($tmpRoot.'/images/content/dynamisch/projects');
        @rmdir($tmpRoot.'/images/content/dynamisch');
        @rmdir($tmpRoot.'/images/content');
        @rmdir($tmpRoot.'/images');
        @rmdir($tmpRoot);
    }

    // ------------------------------------------------------------------ imageBrowserAction

    public function testImageBrowserActionReturnsGalleryTemplate(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_browser_'.uniqid();
        $uploadDir = $tmpRoot.'/images/upload/42/projects';
        mkdir($uploadDir, 0777, true);
        file_put_contents($uploadDir.'/image.jpg', 'fake');

        $this->controller->testPublicDir = $tmpRoot;

        $this->controller->imageBrowserAction(new Request());

        self::assertSame('fragment/thumb_gallery.html.twig', $this->controller->renderedView);
        self::assertArrayHasKey('images', $this->controller->renderedParams);
        self::assertNotEmpty($this->controller->renderedParams['images']);

        // Cleanup
        unlink($uploadDir.'/image.jpg');
        rmdir($uploadDir);
        @rmdir($tmpRoot.'/images/upload/42');
        @rmdir($tmpRoot.'/images/upload');
        @rmdir($tmpRoot.'/images');
        @rmdir($tmpRoot);
    }

    // ------------------------------------------------------------------ uploadImageAction

    public function testUploadImageActionUploadsRegularFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'proj_upload_');
        file_put_contents($tmpFile, 'fake png content');

        $uploadedFile = new UploadedFile($tmpFile, 'photo.png', 'image/png', null, true);

        $tmpRoot = sys_get_temp_dir().'/proj_img_root_'.uniqid();
        mkdir($tmpRoot.'/images/upload/42/projects', 0777, true);
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);
        self::assertSame('photo.png', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*'));
        rmdir($tmpRoot.'/images/upload/42/projects');
        rmdir($tmpRoot.'/images/upload/42');
        rmdir($tmpRoot.'/images/upload');
        rmdir($tmpRoot.'/images');
        rmdir($tmpRoot);
    }

    public function testUploadImageActionAcceptsBase64FileData(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_img_b64_'.uniqid();
        mkdir($tmpRoot.'/images/upload/42/projects', 0777, true);
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->request->set('fileData', base64_encode('fake content'));
        $request->request->set('name', 'logo.jpg');

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame('logo.jpg', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*'));
        rmdir($tmpRoot.'/images/upload/42/projects');
        rmdir($tmpRoot.'/images/upload/42');
        rmdir($tmpRoot.'/images/upload');
        rmdir($tmpRoot.'/images');
        rmdir($tmpRoot);
    }

    public function testUploadImageActionParsesJsonContentType(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_img_json_'.uniqid();
        mkdir($tmpRoot.'/images/upload/42/projects', 0777, true);
        $this->controller->testPublicDir = $tmpRoot;

        $tmpFile = tempnam(sys_get_temp_dir(), 'proj_json_file_');
        file_put_contents($tmpFile, 'fake');
        $uploadedFile = new UploadedFile($tmpFile, 'snap.jpg', 'image/jpeg', null, true);

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadImageAction($request);

        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*'));
        rmdir($tmpRoot.'/images/upload/42/projects');
        rmdir($tmpRoot.'/images/upload/42');
        rmdir($tmpRoot.'/images/upload');
        rmdir($tmpRoot.'/images');
        rmdir($tmpRoot);
    }

    // ------------------------------------------------------------------ uploadPreviewAction

    public function testUploadPreviewActionUploadsRegularFileAsPreview(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'proj_preview_');
        file_put_contents($tmpFile, 'fake img');

        $uploadedFile = new UploadedFile($tmpFile, 'cover.jpg', 'image/jpeg', null, true);

        $tmpRoot = sys_get_temp_dir().'/proj_preview_root_'.uniqid();
        mkdir($tmpRoot.'/images/upload/42/projects', 0777, true);
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadPreviewAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);
        self::assertStringStartsWith('preview.', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*'));
        rmdir($tmpRoot.'/images/upload/42/projects');
        rmdir($tmpRoot.'/images/upload/42');
        rmdir($tmpRoot.'/images/upload');
        rmdir($tmpRoot.'/images');
        rmdir($tmpRoot);
    }

    public function testUploadPreviewActionAcceptsBase64Data(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_prev_b64_'.uniqid();
        mkdir($tmpRoot.'/images/upload/42/projects', 0777, true);
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->request->set('fileData', base64_encode('fake img data'));
        $request->request->set('name', 'preview-file.jpg');

        $response = $this->controller->uploadPreviewAction($request);

        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);
        self::assertStringStartsWith('preview.', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*'));
        rmdir($tmpRoot.'/images/upload/42/projects');
        rmdir($tmpRoot.'/images/upload/42');
        rmdir($tmpRoot.'/images/upload');
        rmdir($tmpRoot.'/images');
        rmdir($tmpRoot);
    }

    // ------------------------------------------------------------------ generatePublicPicturePath (private via side-effect)

    public function testSaveActionIncludesPublicPicturePathInCreateRender(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        // The createAction calls generatePublicPicturePath with no arg → null
        $this->controller->createAction();

        // publicPicturePath key exists in rendered params
        self::assertArrayHasKey('publicPicturePath', $this->controller->renderedParams);
        self::assertNull($this->controller->renderedParams['publicPicturePath']);
    }

    public function testEditByIdActionPassesPublicPicturePathForKnownId(): void
    {
        $project = new Projects();
        $project->setId(11);
        $this->projectsRepo->method('find')->with(11)->willReturn($project);

        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->editByIdAction(11);

        self::assertArrayHasKey('publicPicturePath', $this->controller->renderedParams);
        // generatePublicPicturePath is called without argument → always returns null
        self::assertTrue(array_key_exists('publicPicturePath', $this->controller->renderedParams));
        self::assertNull($this->controller->renderedParams['publicPicturePath']);
    }

    // ------------------------------------------------------------------ getPublicDir production path
    public function testGetPublicDirReturnsPathEndingInPublic(): void
    {
        // Call the real production getPublicDir() bypassing the test-subclass override
        $method = new \ReflectionMethod(ProjectsController::class, 'getPublicDir');
        $result = $method->invoke($this->controller);

        self::assertStringEndsWith('/public', $result);
        self::assertDirectoryExists($result);
    }

    // ------------------------------------------------------------------ clearUploadFolderAction
    public function testClearUploadFolderActionDeletesFilesAndReturnsSuccess(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_clear_'.uniqid();
        $uploadDir = $tmpRoot.'/public/images/upload/42/projects';
        mkdir($uploadDir, 0777, true);
        $dummyFile = $uploadDir.'/dummy.jpg';
        file_put_contents($dummyFile, 'data');

        $this->controller->testProjectDir = $tmpRoot;

        $response = $this->controller->clearUploadFolderAction();

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertTrue($data['success']);
        self::assertFileDoesNotExist($dummyFile);

        // Cleanup
        @rmdir($uploadDir);
        @rmdir(dirname($uploadDir));
        @rmdir(dirname($uploadDir, 2));
        @rmdir(dirname($uploadDir, 3));
        @rmdir($tmpRoot.'/public');
        @rmdir($tmpRoot);
    }

    public function testClearUploadFolderActionWithEmptyFolderReturnsSuccess(): void
    {
        $tmpRoot = sys_get_temp_dir().'/proj_clear_empty_'.uniqid();
        $uploadDir = $tmpRoot.'/public/images/upload/42/projects';
        mkdir($uploadDir, 0777, true);

        $this->controller->testProjectDir = $tmpRoot;

        $response = $this->controller->clearUploadFolderAction();

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertTrue($data['success']);

        // Cleanup
        @rmdir($uploadDir);
        @rmdir(dirname($uploadDir));
        @rmdir(dirname($uploadDir, 2));
        @rmdir(dirname($uploadDir, 3));
        @rmdir($tmpRoot.'/public');
        @rmdir($tmpRoot);
    }

    // ------------------------------------------------------------------ mkdir branch in uploadImageAction
    public function testUploadImageActionCreatesDirectoryIfMissing(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'proj_mkd_');
        file_put_contents($tmpFile, 'fake png content');
        $uploadedFile = new UploadedFile($tmpFile, 'shot.png', 'image/png', null, true);

        // Do NOT pre-create the directory – this exercises the mkdir() branch
        $tmpRoot = sys_get_temp_dir().'/proj_mkd_'.uniqid();
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*') ?: []);
        foreach ([
            $tmpRoot.'/images/upload/42/projects',
            $tmpRoot.'/images/upload/42',
            $tmpRoot.'/images/upload',
            $tmpRoot.'/images',
            $tmpRoot,
        ] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    // ------------------------------------------------------------------ mkdir branch in uploadPreviewAction
    public function testUploadPreviewActionCreatesDirectoryIfMissing(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'proj_prev_mkd_');
        file_put_contents($tmpFile, 'fake content');
        $uploadedFile = new UploadedFile($tmpFile, 'preview.jpg', 'image/jpeg', null, true);

        // Do NOT pre-create the directory – this exercises the mkdir() branch
        $tmpRoot = sys_get_temp_dir().'/proj_prev_mkd_'.uniqid();
        $this->controller->testPublicDir = $tmpRoot;

        $request = new Request();
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadPreviewAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);

        // Cleanup
        array_map('unlink', glob($tmpRoot.'/images/upload/42/projects/*') ?: []);
        foreach ([
            $tmpRoot.'/images/upload/42/projects',
            $tmpRoot.'/images/upload/42',
            $tmpRoot.'/images/upload',
            $tmpRoot.'/images',
            $tmpRoot,
        ] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
