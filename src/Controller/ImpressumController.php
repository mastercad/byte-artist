<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ImpressumController extends AbstractController
{
    /**
     * @Route ("/imprint", name="imprint")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('impressum/index.html.twig', [
            'controller_name' => 'ImpressumController',
        ]);
    }
}
