<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blogs;
use App\Entity\BlogTags;
use App\Entity\Tags;
use App\Form\BlogType;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    public const ITEMS_PER_PAGE = 10;

    /**
     * @Route("/blog", name="blog")
     */
    public function indexAction(EntityManagerInterface $entityManager, Request $request, Pagination $pagination)
    {
        $blogTags = $this->getDoctrine()->getRepository(BlogTags::class)->findAll();

        $query = $entityManager->getRepository(Blogs::class)->queryAllVisibleBlogs();
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

    /**
     * @Route(
     *      "/blog/tag/{tagSeoLink}",
     *      name="blog_tag_landing",
     *      methods={"GET"},
     *      requirements={"tagSeoLink"="[a-z0-9\_\-]+"}
     * )
     */
    public function tagAction(
        EntityManagerInterface $entityManager,
        Request $request,
        Pagination $pagination,
        string $tagSeoLink
    ) {
        $blogTags = $this->getDoctrine()->getRepository(BlogTags::class)->findAll();

        $query = $entityManager->getRepository(Blogs::class)->queryAllBlogsByTag($tagSeoLink);
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

    /**
     * @Route("/blog/create/{id}", name="blog_create", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function createAction(EntityManagerInterface $entityManager, Request $request, int $id = null)
    {
        $blog = null;
        $tags = $this->getDoctrine()->getRepository(Tags::class)->findAll();

        if (0 < $id) {
            $blog = $this->getDoctrine()->getRepository(Blogs::class)->find($id);
            $blog->setModified(new \DateTime());
            $blog->setModifier($this->getUser());
        } else {
            $blog = new Blogs();
            $blog->setCreated(new \DateTime());
            $blog->setCreator($this->getUser());
        }

        $this->denyAccessUnlessGranted('edit', $blog);

        // Remove and persist current BlogTag Request
        $blogRequest = $request->request->get('blog');
        $currentBlogTagsRequest = [];

        if (is_array($blogRequest)
            && array_key_exists('blogTags', $blogRequest)
        ) {
            $currentBlogTagsRequest = $blogRequest['blogTags'];
            unset($blogRequest['blogTags']);
        }
        $request->request->set('blog', $blogRequest);

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted()
            && $form->isValid()
        ) {
            $entityManager->persist($blog);
            $entityManager->flush();

            $blog = $this->considerTags($entityManager, $blog, $currentBlogTagsRequest);
        }

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView(),
            'availableTags' => $tags,
        ]);
    }

    private function considerTags(EntityManagerInterface $entityManager, $blog, $blogTags)
    {
        $currentBlogTags = $this->getDoctrine()->getRepository(BlogTags::class)->findBy(['blog' => $blog]);
        $oldBlogTags = [];
        foreach ($currentBlogTags as $currentBlogTag) {
            $oldBlogTags[$currentBlogTag->getId()] = $currentBlogTag;
        }

        foreach ($blogTags as $blogTag) {
            $blogTagEntity = null;
            $tagEntity = null;

            // blog tag id exists
            if (isset($blogTag['id'])
                && !empty($blogTag['id'])
                && 'undefined' != $blogTag['id']
            ) {
                unset($oldBlogTags[$blogTag['id']]);
                continue;
            }

            if (isset($blogTag['tagId'])
                && !empty($blogTag['tagId'])
                && 'undefined' != $blogTag['tagId']
            ) {
                $tagEntity = $this->getDoctrine()->getRepository(Tags::class)->find($blogTag['tagId']);
            } else {
                $tagEntity = new Tags();
                $tagEntity->setCreator($this->getUser());
                $tagEntity->setCreated(new \DateTime());
                $tagEntity->setName($blogTag['tagName']);
                $tagEntity->setSeoLink(strtolower($blogTag['tagName']));
                $entityManager->persist($tagEntity);
                $entityManager->flush();
            }
            $blogTagEntity = new BlogTags();
            $blogTagEntity->setCreated(new \DateTime());
            $blogTagEntity->setCreator($this->getUser());
            $blogTagEntity->setTag($tagEntity);
            $blogTagEntity->setBlog($blog);
            $entityManager->persist($blogTagEntity);
            $blog->addBlogTag($blogTagEntity);
            $entityManager->flush();
        }

        foreach ($oldBlogTags as $oldBlogTag) {
            $entityManager->remove($oldBlogTag);
            $entityManager->flush();
        }

        return $blog;
    }

    /**
     * @Route("/blog/{id}", name="blog_detail_by_id", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showAction(int $id)
    {
        $blog = $this->getDoctrine()->getRepository(Blogs::class)->find($id);

//        $this->isGranted('view', $blog);
        $this->denyAccessUnlessGranted('show', $blog);

        return $this->render(
            'blog/show.html.twig',
            [
                'blog' => $blog,
            ]
        );
    }

    /**
     * @Route("/blog/{name}", name="blog_detail_by_name", methods={"GET"}, requirements={"name"="[a-z0-9\_\-]+"})
     */
    public function detailByNameAction(string $name)
    {
        $blog = $this->getDoctrine()->getRepository(Blogs::class)->findOneBy(['seoLink' => $name]);

//        $this->isGranted('show', $blog);
        $this->denyAccessUnlessGranted('show', $blog);

        return $this->render(
            'blog/show.html.twig',
            [
                'blog' => $blog,
            ]
        );
    }
}
