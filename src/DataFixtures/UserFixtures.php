<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userEntity = new User;
        $userEntity->setEmail('testuser@email.de');
        $userEntity->setRoles(['testRole']);
        $userEntity->setUsername('TestUserName1');
        $manager->persist($userEntity);

        $manager->flush();
    }
}
