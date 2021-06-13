<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DataProtectionController extends AbstractController
{
    /**
     * @Route ("/data-protection", name="data_protection")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('data_protection/index.html.twig', [
            'controller_name' => 'DataProtectionController',
        ]);
    }
}
