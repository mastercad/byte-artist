<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImpressumController extends AbstractController
{
    /**
     * @Route ("/imprint", name="imprint")
     */
    public function index(): Response
    {
        return $this->render('impressum/index.html.twig', [
            'controller_name' => 'ImpressumController',
        ]);
    }
}
