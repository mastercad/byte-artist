<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Blogs;
use App\Entity\Projects;
use App\Entity\User;
use App\Security\Voter\ProjectVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProjectVoterTest extends TestCase
{
    private ProjectVoter $voter;
    /** @var MockObject&Security */
    private MockObject $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->voter = new ProjectVoter($this->createMock(LoggerInterface::class), $this->security);
    }

    // ------------------------------------------------------------------ supports

    public function testSupportsEditWithProjectsReturnsTrue(): void
    {
        self::assertTrue($this->callSupports('edit', new Projects()));
    }

    public function testSupportsShowWithProjectsReturnsTrue(): void
    {
        self::assertTrue($this->callSupports('show', new Projects()));
    }

    public function testSupportsDeleteWithProjectsReturnsTrue(): void
    {
        self::assertTrue($this->callSupports('delete', new Projects()));
    }

    public function testSupportsUnknownAttributeReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('view', new Projects()));
    }

    public function testSupportsWrongSubjectTypeReturnsFalse(): void
    {
        // Blogs is not Projects
        self::assertFalse($this->callSupports('edit', new Blogs()));
    }

    public function testSupportsNonObjectSubjectReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('edit', 'not-an-entity'));
    }

    public function testSupportsNullSubjectReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('edit', null));
    }

    // ------------------------------------------------------------------ voteOnAttribute: admin shortcuts

    public function testSuperAdminIsGrantedEditWithoutFurtherChecks(): void
    {
        $this->security->expects(self::once())
            ->method('isGranted')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);

        self::assertTrue($this->callVoteOnAttribute('edit', new Projects(), $this->makeToken(null)));
    }

    public function testAdminIsGrantedWhenSuperAdminReturnsFalse(): void
    {
        $this->security->method('isGranted')
            ->willReturnOnConsecutiveCalls(false, true); // ROLE_SUPER_ADMIN=false, ROLE_ADMIN=true

        self::assertTrue($this->callVoteOnAttribute('edit', new Projects(), $this->makeToken(null)));
    }

    public function testSuperAdminIsGrantedDeleteWithoutFurtherChecks(): void
    {
        $this->security->expects(self::once())
            ->method('isGranted')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);

        self::assertTrue($this->callVoteOnAttribute('delete', new Projects(), $this->makeToken(null)));
    }

    public function testSuperAdminIsGrantedShowWithoutFurtherChecks(): void
    {
        $this->security->expects(self::once())
            ->method('isGranted')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);

        self::assertTrue($this->callVoteOnAttribute('show', new Projects(), $this->makeToken(null)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: edit branch

    public function testEditReturnsFalseWhenTokenUserIsNull(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        self::assertFalse($this->callVoteOnAttribute('edit', new Projects(), $this->makeToken(null)));
    }

    public function testEditReturnsTrueWhenCreatorStrictEqualsTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        $project = new Projects();
        $project->setCreator($user);

        // ProjectVoter uses === (strict) - same object instance must be used
        self::assertTrue($this->callVoteOnAttribute('edit', $project, $this->makeToken($user)));
    }

    public function testEditReturnsFalseWhenCreatorDiffersFromTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $creator */
        $creator = $this->createMock(User::class);
        /** @var MockObject&User $otherUser */
        $otherUser = $this->createMock(User::class);
        $project = new Projects();
        $project->setCreator($creator);

        self::assertFalse($this->callVoteOnAttribute('edit', $project, $this->makeToken($otherUser)));
    }

    public function testEditReturnsFalseWhenProjectCreatorIsNull(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        $project = new Projects(); // creator is null

        self::assertFalse($this->callVoteOnAttribute('edit', $project, $this->makeToken($user)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: delete branch (same as edit)

    public function testDeleteReturnsFalseWhenTokenUserIsNull(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        self::assertFalse($this->callVoteOnAttribute('delete', new Projects(), $this->makeToken(null)));
    }

    public function testDeleteReturnsTrueWhenCreatorStrictEqualsTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        $project = new Projects();
        $project->setCreator($user);

        self::assertTrue($this->callVoteOnAttribute('delete', $project, $this->makeToken($user)));
    }

    public function testDeleteReturnsFalseWhenCreatorDiffersFromTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $creator */
        $creator = $this->createMock(User::class);
        /** @var MockObject&User $other */
        $other = $this->createMock(User::class);
        $project = new Projects();
        $project->setCreator($creator);

        self::assertFalse($this->callVoteOnAttribute('delete', $project, $this->makeToken($other)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: show branch

    public function testShowAlwaysReturnsTrueForAuthenticatedUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);

        self::assertTrue($this->callVoteOnAttribute('show', new Projects(), $this->makeToken($user)));
    }

    public function testShowAlwaysReturnsTrueForAnonymousToken(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        self::assertTrue($this->callVoteOnAttribute('show', new Projects(), $this->makeToken(null)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: default (unsupported in switch)

    public function testUnsupportedAttributeFallsThroughToFalse(): void
    {
        $this->security->method('isGranted')->willReturn(false);
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);

        // 'view' is not a case in the switch
        self::assertFalse($this->callVoteOnAttribute('view', new Projects(), $this->makeToken($user)));
    }

    // ------------------------------------------------------------------ strict-equality edge case

    public function testEditFailsWithEqualButNotIdenticalUserObjects(): void
    {
        // ProjectVoter uses === (strict), so two User mocks that are "equal"
        // but not the same instance must NOT grant access.
        $this->security->method('isGranted')->willReturn(false);

        $creator = new User();
        $otherUser = new User(); // separate instance, same class/state
        $project = new Projects();
        $project->setCreator($creator);

        self::assertFalse($this->callVoteOnAttribute('edit', $project, $this->makeToken($otherUser)));
    }

    // ------------------------------------------------------------------ helpers

    private function makeToken(mixed $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    private function callSupports(string $attribute, mixed $subject): bool
    {
        return (new \ReflectionMethod(ProjectVoter::class, 'supports'))
            ->invoke($this->voter, $attribute, $subject);
    }

    private function callVoteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return (new \ReflectionMethod(ProjectVoter::class, 'voteOnAttribute'))
            ->invoke($this->voter, $attribute, $subject, $token);
    }
}
