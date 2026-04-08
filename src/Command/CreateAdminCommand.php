<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Erstellt einen Admin-Benutzer oder aktualisiert sein Passwort.')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'E-Mail-Adresse des Admins')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Passwort (sonst interaktive Abfrage)')
            ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'Benutzername');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email') ?? $io->ask('E-Mail', 'admin@byte-artist.de');
        $username = $input->getOption('username') ?? $io->ask('Benutzername', 'admin');
        $password = $input->getOption('password') ?? $io->askHidden('Passwort');

        if (empty($password)) {
            $io->error('Passwort darf nicht leer sein.');

            return Command::FAILURE;
        }

        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['email' => $email]) ?? new User();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Admin "%s" (%s) erfolgreich gespeichert.', $username, $email));

        return Command::SUCCESS;
    }
}
