<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blogs;
use App\Entity\Projects;
use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        /** @var BlogRepository $blogRepository */
        $blogRepository = $this->getDoctrine()->getRepository(Blogs::class);
        $blogs = $blogRepository->findLatest(0, 3);
        $projects = $this->getDoctrine()->getRepository(Projects::class)->findBy(
            [],
            ['modified' => 'DESC', 'created' => 'DESC'],
            3
        );

        return $this->render('index/index.html.twig', [
            'blogs' => $blogs,
            'projects' => $projects,
        ]);
    }
}
