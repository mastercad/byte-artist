<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blogs;
use App\Entity\BlogTags;
use App\Entity\Tags;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use App\Service\File\Base64EncodedFile;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    public const ITEMS_PER_PAGE = 10;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/blog', name: 'blog')]
    public function indexAction(
        Request $request,
        Pagination $pagination
    ): Response {
        $blogTags = $this->em->getRepository(BlogTags::class)->findAll();
        /** @var BlogRepository $blogRepository */
        $blogRepository = $this->em->getRepository(Blogs::class);
        $query = $blogRepository->queryAllVisibleBlogs();
        $blogs = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);

        return $this->render(
            'blog/index.html.twig',
            [
                'blogs' => $blogs,
                'blogTags' => $blogTags,
                'lastPage' => $pagination->lastPage($blogs),
            ]
        );
    }

    #[Route('/blog/tag/{tagSeoLink}', name: 'blog_tag_landing', methods: ['GET'], requirements: ['tagSeoLink' => '[a-z0-9\_\-]+'])]
    public function tagAction(
        Request $request,
        Pagination $pagination,
        string $tagSeoLink
    ): Response {
        $blogTags = $this->em->getRepository(BlogTags::class)->findAll();
        /** @var BlogRepository $blogRepository */
        $blogRepository = $this->em->getRepository(Blogs::class);
        $query = $blogRepository->queryAllBlogsByTag($tagSeoLink);
        $blogs = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);

        return $this->render(
            'blog/index.html.twig',
            [
                'blogs' => $blogs,
                'blogTags' => $blogTags,
                'lastPage' => $pagination->lastPage($blogs),
                'tagSeoLink' => $tagSeoLink,
            ]
        );
    }

    #[Route('/blog/create/{id}', name: 'blog_create', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function createAction(
        Request $request,
        ?int $id = null
    ): Response {
        $tags = $this->em->getRepository(Tags::class)->findAll();

        if (0 < $id) {
            $blog = $this->em->getRepository(Blogs::class)->find($id);
            $blog->setModified(new \DateTime());
            $blog->setModifier($this->getUser());
        } else {
            $blog = new Blogs();
            $blog->setCreator($this->getUser());
        }

        $this->denyAccessUnlessGranted('edit', $blog);

        $blogRequest = $request->request->all('blog');
        $currentBlogTagsRequest = [];

        if (is_array($blogRequest) && array_key_exists('blogTags', $blogRequest)) {
            $currentBlogTagsRequest = is_array($blogRequest['blogTags']) ? $blogRequest['blogTags'] : [];
            unset($blogRequest['blogTags']);
        }

        // For new entries with no submitted date, default to now.
        if (0 >= $id && empty($blogRequest['created'])) {
            $blog->setCreated(new \DateTime());
        }

        $request->request->set('blog', $blogRequest);

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            if (!$form->isValid()) {
                return new JsonResponse($this->extractErrorsFromForm($form), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $this->em->persist($blog);
            $this->em->flush();
            // Blog is now persisted – capture ID before any post-save step can fail.
            $blogId = $blog->getId();
            try {
                $blog = $this->considerTags($blog, $currentBlogTagsRequest);
            } catch (\Throwable $e) {
                return new JsonResponse(['id' => $blogId, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse(['id' => $blog->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($blog);
            $this->em->flush();
            $blog = $this->considerTags($blog, $currentBlogTagsRequest);
        }

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView(),
            'availableTags' => $tags,
        ]);
    }

    protected function getPublicDir(): string
    {
        return __DIR__.'/../../public';
    }

    private function extractErrorsFromForm(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->extractErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    private function considerTags(Blogs $blog, array $blogTags): Blogs
    {
        $currentBlogTags = $this->em->getRepository(BlogTags::class)->findBy(['blog' => $blog]);
        $oldBlogTags = [];
        foreach ($currentBlogTags as $currentBlogTag) {
            $oldBlogTags[$currentBlogTag->getId()] = $currentBlogTag;
        }

        foreach ($blogTags as $blogTag) {
            if (isset($blogTag['id']) && !empty($blogTag['id']) && 'undefined' !== $blogTag['id']) {
                unset($oldBlogTags[$blogTag['id']]);
                continue;
            }

            if (isset($blogTag['tagId']) && !empty($blogTag['tagId']) && 'undefined' !== $blogTag['tagId']) {
                $tagEntity = $this->em->getRepository(Tags::class)->find($blogTag['tagId']);
            } else {
                $tagName = trim($blogTag['tagName'] ?? '');
                if ('' === $tagName) {
                    continue;
                }
                $tagEntity = $this->em->getRepository(Tags::class)->findOneBy(['name' => $tagName]);
                if (!$tagEntity) {
                    $tagEntity = new Tags();
                    $tagEntity->setCreator($this->getUser());
                    $tagEntity->setCreated(new \DateTime());
                    $tagEntity->setName($tagName);
                    $tagEntity->setSeoLink(strtolower($tagName));
                    $this->em->persist($tagEntity);
                    $this->em->flush();
                }
            }

            $blogTagEntity = new BlogTags();
            $blogTagEntity->setCreated(new \DateTime());
            $blogTagEntity->setCreator($this->getUser());
            $blogTagEntity->setTag($tagEntity);
            $blogTagEntity->setBlog($blog);
            $this->em->persist($blogTagEntity);
            $blog->addBlogTag($blogTagEntity);
            $this->em->flush();
        }

        foreach ($oldBlogTags as $oldBlogTag) {
            $this->em->remove($oldBlogTag);
            $this->em->flush();
        }

        return $blog;
    }

    #[Route('/blog/{id}', name: 'blog_detail_by_id', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showAction(int $id): Response
    {
        $blog = $this->em->getRepository(Blogs::class)->find($id);
        $this->denyAccessUnlessGranted('show', $blog);

        return $this->render('blog/show.html.twig', ['blog' => $blog]);
    }

    #[Route('/blog/{name}', name: 'blog_detail_by_name', methods: ['GET'], requirements: ['name' => '[a-z0-9\_\-]+'])]
    public function detailByNameAction(string $name): Response
    {
        $blog = $this->em->getRepository(Blogs::class)->findOneBy(['seoLink' => $name]);
        $this->denyAccessUnlessGranted('show', $blog);

        return $this->render('blog/show.html.twig', ['blog' => $blog]);
    }

    #[Route('/blog/upload', name: 'app_blog_image_upload', methods: ['POST'])]
    public function uploadImageAction(Request $request): JsonResponse
    {
        if (str_starts_with((string) $request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : []);
        }

        $file = $request->files->get('upload');
        if (null === $file && null !== $request->get('fileData')) {
            $file = new Base64EncodedFile($request->get('fileData'));
            $originalFileName = $request->get('name') ?: 'image.jpg';
        } else {
            $originalFileName = $file->getClientOriginalName();
        }

        $publicUploadPath = '/images/upload/'.$this->getUser()->getId().'/blogs';
        $targetPath = $this->getPublicDir().$publicUploadPath;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        $file->move($targetPath, $originalFileName);

        return new JsonResponse([
            'uploaded' => 1,
            'fileName' => $originalFileName,
            'url' => $publicUploadPath.'/'.$originalFileName,
        ]);
    }
}
