<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\BlogController;
use App\Entity\Blogs;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Test subclass that exposes private methods and breaks the Symfony
 * container dependency so no kernel is needed.
 */
class TestableBlogController extends BlogController
{
    public ?UserInterface $testUser = null;
    public string $renderedView = '';
    public array $renderedParams = [];
    public ?FormInterface $formToReturn = null;
    public bool $isGrantedResult = true;
    public string $testPublicDir = '';
    public ?object $lastFormData = null;

    protected function getUser(): ?UserInterface
    {
        return $this->testUser;
    }

    protected function denyAccessUnlessGranted(
        mixed $attribute,
        mixed $subject = null,
        string $message = 'Access Denied.'
    ): void {
        // no-op – access checks are covered by BlogVoterTest
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->isGrantedResult;
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->renderedView   = $view;
        $this->renderedParams = $parameters;

        return $response ?? new Response();
    }

    protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
    {
        $this->lastFormData = $data;

        return $this->formToReturn;
    }

    protected function getPublicDir(): string
    {
        return $this->testPublicDir;
    }

    public function callConsiderTags(Blogs $blog, array $blogTags): Blogs
    {
        return (new \ReflectionMethod(BlogController::class, 'considerTags'))
            ->invoke($this, $blog, $blogTags);
    }

    public function callExtractErrorsFromForm(FormInterface $form): array
    {
        return (new \ReflectionMethod(BlogController::class, 'extractErrorsFromForm'))
            ->invoke($this, $form);
    }
}
