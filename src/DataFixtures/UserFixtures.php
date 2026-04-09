<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $existing = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@byte-artist.de']);
        if (null !== $existing) {
            return;
        }

        $admin = new User();
        $admin->setEmail('admin@byte-artist.de');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        $plainPassword = $_ENV['ADMIN_INITIAL_PASSWORD'] ?? throw new \RuntimeException('ADMIN_INITIAL_PASSWORD env var is not set.');
        $admin->setPassword($this->hasher->hashPassword($admin, $plainPassword));

        $manager->persist($admin);
        $manager->flush();
    }
}
