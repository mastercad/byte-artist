<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogRepositoryTest extends WebTestCase
{
    public function testFindLatest()
    {
        $this->loadFixtures(
            [
                UserFixtures::class
            ]
        );
    }
}