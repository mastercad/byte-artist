<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Blogs;
use App\Entity\Projects;
use App\Entity\User;
use App\Security\Voter\BlogVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BlogVoterTest extends TestCase
{
    private BlogVoter $voter;
    /** @var MockObject&Security */
    private MockObject $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->voter = new BlogVoter($this->createMock(LoggerInterface::class), $this->security);
    }

    // ------------------------------------------------------------------ supports

    public function testSupportsEditWithBlogsReturnsTrue(): void
    {
        self::assertTrue($this->callSupports('edit', new Blogs()));
    }

    public function testSupportsShowWithBlogsReturnsTrue(): void
    {
        self::assertTrue($this->callSupports('show', new Blogs()));
    }

    public function testSupportsUnknownAttributeReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('delete', new Blogs()));
    }

    public function testSupportsWrongSubjectTypeReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('edit', new Projects()));
    }

    public function testSupportsNonObjectSubjectReturnsFalse(): void
    {
        self::assertFalse($this->callSupports('show', 'not-an-entity'));
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

        self::assertTrue($this->callVoteOnAttribute('edit', new Blogs(), $this->makeToken(null)));
    }

    public function testAdminIsGrantedWhenSuperAdminReturnsFalse(): void
    {
        $this->security->method('isGranted')
            ->willReturnOnConsecutiveCalls(false, true); // ROLE_SUPER_ADMIN=false, ROLE_ADMIN=true

        self::assertTrue($this->callVoteOnAttribute('edit', new Blogs(), $this->makeToken(null)));
    }

    public function testSuperAdminIsGrantedShowWithoutFurtherChecks(): void
    {
        $this->security->expects(self::once())
            ->method('isGranted')
            ->with('ROLE_SUPER_ADMIN')
            ->willReturn(true);

        self::assertTrue($this->callVoteOnAttribute('show', new Blogs(), $this->makeToken(null)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: edit branch

    public function testEditReturnsFalseWhenTokenUserIsNull(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        self::assertFalse($this->callVoteOnAttribute('edit', new Blogs(), $this->makeToken(null)));
    }

    public function testEditReturnsTrueWhenCreatorEqualsTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        $blog = new Blogs();
        $blog->setCreator($user);

        self::assertTrue($this->callVoteOnAttribute('edit', $blog, $this->makeToken($user)));
    }

    public function testEditReturnsFalseWhenCreatorDiffersFromTokenUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        // Use real User objects with different usernames so PHP's loose == returns false.
        // Two User mocks with all-null properties would compare as == equal (same state).
        $creator = new User();
        $creator->setUsername('creator-user');
        $otherUser = new User();
        $otherUser->setUsername('other-user');
        $blog = new Blogs();
        $blog->setCreator($creator);

        self::assertFalse($this->callVoteOnAttribute('edit', $blog, $this->makeToken($otherUser)));
    }

    public function testEditReturnsFalseWhenBlogCreatorIsNull(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        $blog = new Blogs(); // creator is null by default

        self::assertFalse($this->callVoteOnAttribute('edit', $blog, $this->makeToken($user)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: show branch

    public function testShowAlwaysReturnsTrueForAuthenticatedUser(): void
    {
        $this->security->method('isGranted')->willReturn(false);
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);

        self::assertTrue($this->callVoteOnAttribute('show', new Blogs(), $this->makeToken($user)));
    }

    public function testShowAlwaysReturnsTrueForAnonymousToken(): void
    {
        $this->security->method('isGranted')->willReturn(false);

        self::assertTrue($this->callVoteOnAttribute('show', new Blogs(), $this->makeToken(null)));
    }

    // ------------------------------------------------------------------ voteOnAttribute: default (unsupported attribute)

    public function testUnsupportedAttributeFallsThroughToFalse(): void
    {
        $this->security->method('isGranted')->willReturn(false);
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);

        // 'delete' is not handled in the switch statement of BlogVoter
        self::assertFalse($this->callVoteOnAttribute('delete', new Blogs(), $this->makeToken($user)));
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
        return (new \ReflectionMethod(BlogVoter::class, 'supports'))
            ->invoke($this->voter, $attribute, $subject);
    }

    private function callVoteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return (new \ReflectionMethod(BlogVoter::class, 'voteOnAttribute'))
            ->invoke($this->voter, $attribute, $subject, $token);
    }
}
