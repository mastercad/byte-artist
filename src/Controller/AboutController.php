<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * @Route ("/about", name="about")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $day = 18;
        $month = 11;
        $year = 1975;

        $today = mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));
        $birthday = mktime(0, 0, 0, $month, $day, $year);
        $age = intval(($today - $birthday) / (60 * 60 * 24 * 365));

        return $this->render('about/index.html.twig', [
            'age' => $age,
        ]);
    }
}
