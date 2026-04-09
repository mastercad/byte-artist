<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\ProjectsController;
use App\Entity\Projects;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Test subclass that exposes private methods and removes the Symfony
 * container dependency so no kernel is needed.
 */
class TestableProjectsController extends ProjectsController
{
    public ?UserInterface $testUser = null;
    public string $renderedView = '';
    public array $renderedParams = [];
    public ?FormInterface $formToReturn = null;
    public bool $isGrantedResult = true;
    public string $testPublicDir = '';
    public ?object $lastFormData = null;
    public string $testProjectDir = '';

    protected function getUser(): ?UserInterface
    {
        return $this->testUser;
    }

    protected function denyAccessUnlessGranted(
        mixed $attribute,
        mixed $subject = null,
        string $message = 'Access Denied.'
    ): void {
        // no-op – access checks are covered by ProjectVoterTest
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
        return $this->testPublicDir ?: sys_get_temp_dir();
    }

    protected function handleUploadFiles(Projects $project): static
    {
        return $this; // no-op – avoids filesystem operations in unit tests
    }

    protected function clearUploadFolder(): static
    {
        return $this; // no-op – avoids filesystem operations in unit tests
    }

    public function getParameter(string $name): \UnitEnum|array|string|int|float|bool|null
    {
        if ('kernel.project_dir' === $name) {
            return $this->testProjectDir ?: sys_get_temp_dir();
        }

        return null;
    }

    public function callConsiderTags(Projects $project, array $projectTags): Projects
    {
        return (new \ReflectionMethod(ProjectsController::class, 'considerTags'))
            ->invoke($this, $project, $projectTags);
    }

    public function callExtractErrorsFromForm(FormInterface $form): array
    {
        return (new \ReflectionMethod(ProjectsController::class, 'extractErrorsFromForm'))
            ->invoke($this, $form);
    }
}
