<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WYSIWYGController extends AbstractController
{
    /**
     * @Route ("/wysiwyg", name="wysiwyg")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('wysiwyg/index.html.twig');
    }
}
