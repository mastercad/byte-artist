<?php

namespace App\Security\Voter;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter extends Voter
{
    private $security;
    private $logger;

    public function __construct(LoggerInterface $logger, Security $security)
    {
        $this->security = $security;
        $this->logger = $logger;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['edit', 'show', 'delete'])
            && $subject instanceof \App\Entity\Projects;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case 'delete':
            case 'edit':
                if (!$user instanceof UserInterface) {
                    return false;
                }
                if ($subject->getCreator() === $user) {
                    return true;
                }
                break;
            case 'show':
                // visible for all, also unauth users.
                return true;
        }

        return false;
    }
}
