<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogRepositoryTest extends WebTestCase
{
    public function testFindLatest()
    {
        $this->markSkippedForMissingDependecy("loadFixtures is unknown but it should from WebTestCase! Have to Fix it");
        /*
        $this->loadFixtures(
            [
                UserFixtures::class
            ]
        );
        */
    }
}