<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blogs;
use App\Entity\BlogTags;
use App\Entity\Tags;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $blog->setCreated(new \DateTime());
            $blog->setCreator($this->getUser());
        }

        $this->denyAccessUnlessGranted('edit', $blog);

        $blogRequest = $request->request->get('blog');
        $currentBlogTagsRequest = [];

        if (is_array($blogRequest) && array_key_exists('blogTags', $blogRequest)) {
            $currentBlogTagsRequest = $blogRequest['blogTags'];
            unset($blogRequest['blogTags']);
        }
        $request->request->set('blog', $blogRequest);

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

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
                $tagEntity = new Tags();
                $tagEntity->setCreator($this->getUser());
                $tagEntity->setCreated(new \DateTime());
                $tagEntity->setName($blogTag['tagName']);
                $tagEntity->setSeoLink(strtolower($blogTag['tagName']));
                $this->em->persist($tagEntity);
                $this->em->flush();
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
}
