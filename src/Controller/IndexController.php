<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Blogs;
use App\Entity\Projects;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        /** @var BlogRepository $blogRepository */
        $blogRepository = $this->em->getRepository(Blogs::class);
        $blogs = $blogRepository->findLatest(0, 3);
        $projectFilter = $this->isGranted('ROLE_ADMIN') ? [] : ['isPublic' => true];
        $projects = $this->em->getRepository(Projects::class)->findBy(
            $projectFilter,
            ['modified' => 'DESC', 'created' => 'DESC'],
            3
        );

        return $this->render('index/index.html.twig', [
            'blogs' => $blogs,
            'projects' => $projects,
        ]);
    }
}
