<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Blogs;
use App\Entity\BlogTags;
use App\Entity\Tags;
use App\Entity\User;
use App\Repository\BlogRepository;
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

class BlogControllerTest extends TestCase
{
    /** @var MockObject&EntityManagerInterface */
    private MockObject $em;
    /** @var MockObject&EntityRepository<BlogTags> */
    private MockObject $blogTagRepo;
    /** @var MockObject&EntityRepository<Tags> */
    private MockObject $tagRepo;
    /** @var MockObject&BlogRepository */
    private MockObject $blogRepo;
    private TestableBlogController $controller;
    /** @var MockObject&User */
    private MockObject $user;

    protected function setUp(): void
    {
        $this->blogTagRepo = $this->createMock(EntityRepository::class);
        $this->tagRepo = $this->createMock(EntityRepository::class);
        $this->blogRepo = $this->createMock(BlogRepository::class);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturnCallback(
            fn (string $class) => match ($class) {
                BlogTags::class => $this->blogTagRepo,
                Tags::class => $this->tagRepo,
                Blogs::class => $this->blogRepo,
                default => throw new \InvalidArgumentException("Unexpected: $class"),
            }
        );

        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(42);

        $this->controller = new TestableBlogController($this->em);
        $this->controller->testUser = $this->user;
    }

    // ------------------------------------------------------------------ indexAction

    public function testIndexActionRendersCorrectTemplate(): void
    {
        $pagination = $this->createMock(\App\Service\Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->blogTagRepo->method('findAll')->willReturn([]);
        $this->blogRepo->method('queryAllVisibleBlogs')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->indexAction(new Request(), $pagination);

        self::assertSame('blog/index.html.twig', $this->controller->renderedView);
        self::assertArrayHasKey('blogs', $this->controller->renderedParams);
        self::assertArrayHasKey('blogTags', $this->controller->renderedParams);
        self::assertArrayHasKey('lastPage', $this->controller->renderedParams);
    }

    // ------------------------------------------------------------------ tagAction

    public function testTagActionRendersCorrectTemplateWithTagSeoLink(): void
    {
        $pagination = $this->createMock(\App\Service\Pagination::class);
        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);

        $this->blogTagRepo->method('findAll')->willReturn([]);
        $this->blogRepo->method('queryAllBlogsByTag')->willReturn(
            $this->createMock(\Doctrine\ORM\Query::class)
        );
        $pagination->method('paginate')->willReturn($paginator);
        $pagination->method('lastPage')->willReturn(1.0);

        $this->controller->tagAction(new Request(), $pagination, 'php');

        self::assertSame('blog/index.html.twig', $this->controller->renderedView);
        self::assertSame('php', $this->controller->renderedParams['tagSeoLink']);
    }

    // ------------------------------------------------------------------ createAction: new blog

    public function testCreateActionNewBlogRendersFormWhenNotSubmitted(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->createAction(new Request(), null);

        self::assertSame('blog/create.html.twig', $this->controller->renderedView);
    }

    public function testCreateActionNewBlogSetsCreatedDateWhenMissingFromRequest(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->createAction(new Request(), null);

        // The Blogs entity passed to createForm should have a created date set
        $capturedBlog = $this->controller->lastFormData;
        self::assertNotNull($capturedBlog);
        self::assertInstanceOf(Blogs::class, $capturedBlog);
        self::assertInstanceOf(\DateTime::class, $capturedBlog->getCreated());
    }

    public function testCreateActionNewBlogDoesNotOverrideProvidedCreatedDate(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $request = new Request();
        $request->request->set('blog', ['created' => '2023-01-15']);

        $this->controller->createAction($request, null);

        // created date was provided, so the auto-set branch is NOT taken;
        // entity's created remains null (form would set it, but mock doesn't)
        $capturedBlog = $this->controller->lastFormData;
        self::assertNotNull($capturedBlog);
        self::assertInstanceOf(Blogs::class, $capturedBlog);
        self::assertNull($capturedBlog->getCreated());
    }

    // ------------------------------------------------------------------ createAction: existing blog

    public function testCreateActionExistingBlogFetchesFromRepository(): void
    {
        $existingBlog = new Blogs();
        $reflProp = new \ReflectionProperty(Blogs::class, 'id');
        $reflProp->setAccessible(true);
        $reflProp->setValue($existingBlog, 7);

        $this->blogRepo->method('find')->with(7)->willReturn($existingBlog);

        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $this->controller->createAction(new Request(), 7);

        self::assertSame('blog/create.html.twig', $this->controller->renderedView);
    }

    // ------------------------------------------------------------------ createAction: AJAX + invalid

    public function testCreateActionAjaxInvalidFormReturns422(): void
    {
        $form = $this->makeForm(true, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $request = new Request([], [], [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction($request, null);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    // ------------------------------------------------------------------ createAction: AJAX + valid

    public function testCreateActionAjaxValidFormPersistsAndReturnsId(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');

        $request = new Request([], [], [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction($request, null);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('id', $data);
    }

    // ------------------------------------------------------------------ createAction: AJAX + considerTags throws

    public function testCreateActionAjaxConsiderTagsThrowsReturns500(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        // Trigger exception in considerTags via broken BlogTags repo
        $this->blogTagRepo->method('findBy')->willThrowException(new \RuntimeException('DB died'));

        $request = new Request([], [], [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction($request, null);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $data);
    }

    // ------------------------------------------------------------------ createAction: non-AJAX + valid

    public function testCreateActionNonAjaxValidFormPersistsAndRenders(): void
    {
        $form = $this->makeForm(true, true);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');

        $response = $this->controller->createAction(new Request(), null);

        self::assertSame('blog/create.html.twig', $this->controller->renderedView);
    }

    // ------------------------------------------------------------------ createAction: blogTags in request

    public function testCreateActionExtractsBlogTagsFromRequest(): void
    {
        $form = $this->makeForm(false, false);
        $this->controller->formToReturn = $form;
        $this->tagRepo->method('findAll')->willReturn([]);

        $request = new Request();
        $request->request->set('blog', ['blogTags' => [['tagName' => 'php']]]);

        // No exception – just verifies the branch is entered
        $this->controller->createAction($request, null);

        self::assertSame('blog/create.html.twig', $this->controller->renderedView);
    }

    // ------------------------------------------------------------------ showAction

    public function testShowActionRendersShowTemplate(): void
    {
        $blog = new Blogs();
        $this->blogRepo->method('find')->with(5)->willReturn($blog);

        $this->controller->showAction(5);

        self::assertSame('blog/show.html.twig', $this->controller->renderedView);
        self::assertSame($blog, $this->controller->renderedParams['blog']);
    }

    // ------------------------------------------------------------------ detailByNameAction

    public function testDetailByNameActionRendersShowTemplate(): void
    {
        $blog = new Blogs();
        $this->blogRepo->method('findOneBy')->with(['seoLink' => 'my-blog'])->willReturn($blog);

        $this->controller->detailByNameAction('my-blog');

        self::assertSame('blog/show.html.twig', $this->controller->renderedView);
        self::assertSame($blog, $this->controller->renderedParams['blog']);
    }

    // ------------------------------------------------------------------ uploadImageAction: content-type JSON

    public function testUploadImageActionParsesJsonContentType(): void
    {
        $dir = sys_get_temp_dir().'/blog_upload_test_'.uniqid();
        $this->controller->testPublicDir = sys_get_temp_dir().'/blog_upload_root_'.uniqid();

        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, 'fake image data');

        $uploadedFile = new UploadedFile($tmpFile, 'test.jpg', 'image/jpeg', null, true);

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');
        $request->files->set('upload', $uploadedFile);

        // Point the upload path to a temp dir we can write to
        mkdir($this->controller->testPublicDir.'/images/upload/42/blogs', 0777, true);

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);
        self::assertSame('test.jpg', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($this->controller->testPublicDir.'/images/upload/42/blogs/*'));
        rmdir($this->controller->testPublicDir.'/images/upload/42/blogs');
        rmdir($this->controller->testPublicDir.'/images/upload/42');
        rmdir($this->controller->testPublicDir.'/images/upload');
        rmdir($this->controller->testPublicDir.'/images');
        rmdir($this->controller->testPublicDir);
    }

    // ------------------------------------------------------------------ uploadImageAction: regular file

    public function testUploadImageActionUsesClientOriginalName(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, 'fake image data');

        $uploadedFile = new UploadedFile($tmpFile, 'photo.png', 'image/png', null, true);

        $this->controller->testPublicDir = sys_get_temp_dir().'/blog_upload_root2_'.uniqid();
        mkdir($this->controller->testPublicDir.'/images/upload/42/blogs', 0777, true);

        $request = new Request();
        $request->files->set('upload', $uploadedFile);

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame('photo.png', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($this->controller->testPublicDir.'/images/upload/42/blogs/*'));
        rmdir($this->controller->testPublicDir.'/images/upload/42/blogs');
        rmdir($this->controller->testPublicDir.'/images/upload/42');
        rmdir($this->controller->testPublicDir.'/images/upload');
        rmdir($this->controller->testPublicDir.'/images');
        rmdir($this->controller->testPublicDir);
    }

    // ------------------------------------------------------------------ uploadImageAction: base64 fileData

    public function testUploadImageActionAcceptsBase64FileData(): void
    {
        $this->controller->testPublicDir = sys_get_temp_dir().'/blog_upload_root3_'.uniqid();
        mkdir($this->controller->testPublicDir.'/images/upload/42/blogs', 0777, true);

        $base64 = base64_encode('fake png content');

        $request = new Request();
        $request->request->set('fileData', $base64);
        $request->request->set('name', 'custom.jpg');

        $response = $this->controller->uploadImageAction($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        self::assertSame(1, $data['uploaded']);
        self::assertSame('custom.jpg', $data['fileName']);

        // Cleanup
        array_map('unlink', glob($this->controller->testPublicDir.'/images/upload/42/blogs/*'));
        rmdir($this->controller->testPublicDir.'/images/upload/42/blogs');
        rmdir($this->controller->testPublicDir.'/images/upload/42');
        rmdir($this->controller->testPublicDir.'/images/upload');
        rmdir($this->controller->testPublicDir.'/images');
        rmdir($this->controller->testPublicDir);
    }

    // ------------------------------------------------------------------ considerTags: tag with existing id (keep)

    public function testConsiderTagsKeepsTagThatHasKnownId(): void
    {
        $blog = new Blogs();

        /** @var MockObject&BlogTags $existingTag */
        $existingTag = $this->createMock(BlogTags::class);
        $existingTag->method('getId')->willReturn(5);

        $this->blogTagRepo->method('findBy')->willReturn([$existingTag]);

        // Tag with matching id → must NOT be removed
        $this->em->expects(self::never())->method('remove');

        $this->controller->callConsiderTags($blog, [['id' => '5']]);
    }

    public function testConsiderTagsDoesNotCreateNewTagEntityWhenIdIsPresent(): void
    {
        $blog = new Blogs();

        /** @var MockObject&BlogTags $existingTag */
        $existingTag = $this->createMock(BlogTags::class);
        $existingTag->method('getId')->willReturn(7);

        $this->blogTagRepo->method('findBy')->willReturn([$existingTag]);

        // persist should not be called for a new BlogTags when the id shortcuts
        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::never())->method('flush');

        $this->controller->callConsiderTags($blog, [['id' => '7']]);
    }

    // ------------------------------------------------------------------ considerTags: 'undefined' / empty id falls through

    public function testConsiderTagsWithUndefinedIdFallsThroughToTagIdCheck(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        // tagId = 42 → find by id
        $this->tagRepo->method('find')->with('42')->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->controller->callConsiderTags($blog, [
            ['id' => 'undefined', 'tagId' => '42'],
        ]);
    }

    public function testConsiderTagsWithEmptyIdFallsThroughToTagIdCheck(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('find')->with('99')->willReturn($tagEntity);

        $this->em->expects(self::atLeastOnce())->method('persist');

        $this->controller->callConsiderTags($blog, [
            ['id' => '', 'tagId' => '99'],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: tag by tagId

    public function testConsiderTagsByTagIdLooksUpExistingTagEntityAndCreatesBlogTag(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->expects(self::once())
            ->method('find')
            ->with('3')
            ->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $result = $this->controller->callConsiderTags($blog, [
            ['tagId' => '3'],
        ]);

        self::assertCount(1, $result->getBlogTags());
    }

    public function testConsiderTagsWithUndefinedTagIdFallsThroughToNameBranch(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        // tagId = 'undefined' → fall through to name branch; findOneBy called instead
        $this->tagRepo->method('findOneBy')->with(['name' => 'php'])->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');

        $this->controller->callConsiderTags($blog, [
            ['tagId' => 'undefined', 'tagName' => 'php'],
        ]);
    }

    public function testConsiderTagsWithEmptyTagIdFallsThroughToNameBranch(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $tagEntity = new Tags();
        $this->tagRepo->method('findOneBy')->with(['name' => 'symfony'])->willReturn($tagEntity);

        $this->em->expects(self::once())->method('persist');

        $this->controller->callConsiderTags($blog, [
            ['tagId' => '', 'tagName' => 'symfony'],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: tag by name (found)

    public function testConsiderTagsByNameUsesExistingTagWhenFoundInDb(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $existingTag = new Tags();
        $this->tagRepo->method('findOneBy')
            ->with(['name' => 'php'])
            ->willReturn($existingTag);

        // Only one persist call: the new BlogTags entity (no new Tags created)
        $this->em->expects(self::once())->method('persist');

        $result = $this->controller->callConsiderTags($blog, [
            ['tagName' => 'php'],
        ]);

        self::assertCount(1, $result->getBlogTags());
    }

    // ------------------------------------------------------------------ considerTags: tag by name (not found → create)

    public function testConsiderTagsByNameCreatesNewTagEntityWhenNotFoundInDb(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        // findOneBy returns null → new Tags() will be created
        $this->tagRepo->method('findOneBy')->with(['name' => 'new-tag'])->willReturn(null);

        // Two persist calls: once for the new Tags entity, once for the BlogTags entity
        $this->em->expects(self::exactly(2))->method('persist');
        $this->em->expects(self::exactly(2))->method('flush');

        $result = $this->controller->callConsiderTags($blog, [
            ['tagName' => 'new-tag'],
        ]);

        self::assertCount(1, $result->getBlogTags());
    }

    public function testConsiderTagsByNameTrimsTagNameBeforeLookup(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $existingTag = new Tags();
        // Should look up '  php  ' trimmed to 'php'
        $this->tagRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['name' => 'php'])
            ->willReturn($existingTag);

        $this->controller->callConsiderTags($blog, [
            ['tagName' => '  php  '],
        ]);
    }

    // ------------------------------------------------------------------ considerTags: empty tagName skipped

    public function testConsiderTagsSkipsTagWithEmptyTagName(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        // No DB lookups, no persists for empty tagName
        $this->tagRepo->expects(self::never())->method('findOneBy');
        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($blog, [
            ['tagName' => ''],
        ]);
    }

    public function testConsiderTagsSkipsTagWithWhitespaceOnlyName(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        $this->tagRepo->expects(self::never())->method('findOneBy');
        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($blog, [
            ['tagName' => '   '],
        ]);
    }

    public function testConsiderTagsSkipsTagWithMissingTagName(): void
    {
        $blog = new Blogs();
        $this->blogTagRepo->method('findBy')->willReturn([]);

        // blogTag array has no 'tagName' key → trim(null ?? '') = '' → skip
        $this->em->expects(self::never())->method('persist');

        $this->controller->callConsiderTags($blog, [
            [], // no keys at all
        ]);
    }

    // ------------------------------------------------------------------ considerTags: stale tags removed

    public function testConsiderTagsRemovesStaleOldTagsNotInSubmittedList(): void
    {
        $blog = new Blogs();

        /** @var MockObject&BlogTags $staleTag */
        $staleTag = $this->createMock(BlogTags::class);
        $staleTag->method('getId')->willReturn(10);

        $this->blogTagRepo->method('findBy')->willReturn([$staleTag]);

        // No submitted tags → staleTag remains in old map → must be removed
        $this->em->expects(self::once())->method('remove')->with($staleTag);
        $this->em->expects(self::once())->method('flush');

        $this->controller->callConsiderTags($blog, []);
    }

    public function testConsiderTagsRemovesOnlyTagsNotMatchedBySubmittedIds(): void
    {
        $blog = new Blogs();

        /** @var MockObject&BlogTags $keptTag */
        $keptTag = $this->createMock(BlogTags::class);
        $keptTag->method('getId')->willReturn(1);

        /** @var MockObject&BlogTags $staleTag */
        $staleTag = $this->createMock(BlogTags::class);
        $staleTag->method('getId')->willReturn(2);

        $this->blogTagRepo->method('findBy')->willReturn([$keptTag, $staleTag]);

        // Only tag with id=2 must be removed; id=1 is in submitted list
        $this->em->expects(self::once())->method('remove')->with($staleTag);

        $this->controller->callConsiderTags($blog, [
            ['id' => '1'], // keeps keptTag
        ]);
    }

    // ------------------------------------------------------------------ considerTags: multiple tags in one call

    public function testConsiderTagsHandlesMultipleTagsOfDifferentTypes(): void
    {
        $blog = new Blogs();

        /** @var MockObject&BlogTags $existingTag */
        $existingTag = $this->createMock(BlogTags::class);
        $existingTag->method('getId')->willReturn(1);
        $this->blogTagRepo->method('findBy')->willReturn([$existingTag]);

        $newTag = new Tags();
        $this->tagRepo->method('findOneBy')->willReturn($newTag);

        // Two BlogTags.persist + flush calls: one for tagName tag, one for tagId tag
        $tagFromDb = new Tags();
        $this->tagRepo->method('find')->willReturn($tagFromDb);

        $this->controller->callConsiderTags($blog, [
            ['id' => '1'],           // keep existing → no create
            ['tagId' => '5'],        // create BlogTag from tag entity
            ['tagName' => 'php'],    // create BlogTag from name
        ]);

        self::assertCount(2, $blog->getBlogTags());
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
            new FormError('Field is required'),
            new FormError('Value too short'),
        ], []);

        $result = $this->controller->callExtractErrorsFromForm($form);

        self::assertSame(['Field is required', 'Value too short'], $result);
    }

    public function testExtractErrorsFromFormCollectsChildFormErrors(): void
    {
        $childForm = $this->makeMockForm([new FormError('Child error')], [], 'child_field');
        $parentForm = $this->makeMockForm([], [$childForm]);

        $result = $this->controller->callExtractErrorsFromForm($parentForm);

        self::assertArrayHasKey('child_field', $result);
        self::assertSame(['Child error'], $result['child_field']);
    }

    public function testExtractErrorsFromFormExcludesChildFormsWithNoErrors(): void
    {
        $childWithNoErrors = $this->makeMockForm([], [], 'clean_field');
        $parentForm = $this->makeMockForm([], [$childWithNoErrors]);

        $result = $this->controller->callExtractErrorsFromForm($parentForm);

        self::assertArrayNotHasKey('clean_field', $result);
        self::assertSame([], $result);
    }

    public function testExtractErrorsFromFormRecursesIntoNestedChildForms(): void
    {
        $deepChild = $this->makeMockForm([new FormError('Deep error')], [], 'deep');
        $midChild = $this->makeMockForm([], [$deepChild], 'mid');
        $root = $this->makeMockForm([], [$midChild]);

        $result = $this->controller->callExtractErrorsFromForm($root);

        self::assertArrayHasKey('mid', $result);
        self::assertArrayHasKey('deep', $result['mid']);
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
     * Creates a minimal FormInterface mock with the given submitted/valid state.
     */
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

    /**
     * Makes a form that captures the data object passed to createForm().
     * The $capturedData parameter is passed by reference and populated during the test.
     */
    private function makeFormCapturingData(?Blogs &$capturedData, bool $submitted, bool $valid): FormInterface
    {
        return $this->makeForm($submitted, $valid);
    }

    /**
     * Makes a form that captures the data object; sets $ref during the controller call.
     */
    private function makeFormCapturingDataRef(?Blogs &$capturedData, bool $submitted, bool $valid): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn($submitted);
        $form->method('isValid')->willReturn($valid);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('createView')->willReturn(new FormView());
        $form->method('getErrors')->willReturn(new FormErrorIterator($form, []));
        $form->method('all')->willReturn([]);

        // Override createForm in the controller to capture the data
        $controller = $this->controller;
        $controller->formToReturn = $form;

        return $form;
    }

    /**
     * Creates a minimal FormInterface mock with given errors and children.
     *
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
}
