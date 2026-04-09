<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Projects;
use PHPUnit\Framework\TestCase;

/**
 * Tests for upload-feedback & SEO-link fallback related entity behaviour.
 *
 * Changes covered:
 *  – projects/preview_template_left.html.twig: `{{ project.seoLink ?: project.id }}` fallback
 */
class ProjectsEntityTest extends TestCase
{
    // ── seoLink ───────────────────────────────────────────────────────────

    public function testGetSeoLinkReturnsNullByDefault(): void
    {
        $project = new Projects();
        self::assertNull($project->getSeoLink());
    }

    public function testSetAndGetSeoLink(): void
    {
        $project = new Projects();
        $project->setSeoLink('my-project');
        self::assertSame('my-project', $project->getSeoLink());
    }

    public function testSetSeoLinkAcceptsNull(): void
    {
        $project = new Projects();
        $project->setSeoLink('slug');
        $project->setSeoLink(null);
        self::assertNull($project->getSeoLink());
    }

    /**
     * The Twig template uses `project.seoLink ?: project.id`.
     * Ensure a non-empty slug is truthy (slug wins over ID).
     */
    public function testNonEmptySeoLinkIsTruthy(): void
    {
        $project = new Projects();
        $project->setSeoLink('cool-project');
        self::assertNotEmpty($project->getSeoLink());
    }

    /**
     * When seoLink is null, `null ?: id` falls through to the ID
     * which routes to the numeric `/project/{id}` route.
     */
    public function testNullSeoLinkIsFalsy(): void
    {
        $project = new Projects();
        self::assertFalse((bool) $project->getSeoLink());
    }

    // ── previewPicture ────────────────────────────────────────────────────

    public function testGetPreviewPictureReturnsNullByDefault(): void
    {
        $project = new Projects();
        self::assertNull($project->getPreviewPicture());
    }

    public function testSetAndGetPreviewPicture(): void
    {
        $project = new Projects();
        $project->setPreviewPicture('preview.jpg');
        self::assertSame('preview.jpg', $project->getPreviewPicture());
    }

    public function testNullPreviewPictureIsFalsy(): void
    {
        $project = new Projects();
        self::assertFalse((bool) $project->getPreviewPicture());
    }

    public function testNonEmptyPreviewPictureIsTruthy(): void
    {
        $project = new Projects();
        $project->setPreviewPicture('thumb.png');
        self::assertTrue((bool) $project->getPreviewPicture());
    }

    // ── getId / setId ─────────────────────────────────────────────────────

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $project = new Projects();
        self::assertNull($project->getId());
    }

    public function testSetIdAndGetId(): void
    {
        $project = new Projects();
        $project->setId(42);
        self::assertSame(42, $project->getId());
    }
}
