<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WYSIWYGController extends AbstractController
{
    /**
     * @Route("/wysiwyg", name="wysiwyg")
     */
    public function index()
    {
        return $this->render('wysiwyg/index.html.twig');
    }
}
