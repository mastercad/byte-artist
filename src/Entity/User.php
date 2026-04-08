<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'UNIQ_8D93D649F85E0677', columns: ['username'])]
#[ORM\Entity]
#[UniqueEntity('email')]
#[UniqueEntity('username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    private ?string $salt = null;

    #[ORM\Column(name: 'username', type: 'string', length: 180, nullable: false, unique: true)]
    private $username;

    #[ORM\Column(name: 'roles', type: 'json', nullable: false)]
    private $roles = [];

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private $password;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false, unique: true)]
    #[Assert\Email]
    private $email;

    public function __construct()
    {
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        /** @var array $roles */
        $roles = $this->roles;
        // damit mindestens eine Rolle gesetzt wird
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
//        $this->password = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getEmail();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}
