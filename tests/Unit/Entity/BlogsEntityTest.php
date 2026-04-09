<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Blogs;
use PHPUnit\Framework\TestCase;

/**
 * Tests for upload-feedback & SEO-link fallback related entity behaviour.
 *
 * Changes covered:
 *  – blog/preview.html.twig: `{% if blog.previewPicture %}` guard
 *  – blog/preview.html.twig: `{{ blog.seoLink ?: blog.id }}` fallback
 */
class BlogsEntityTest extends TestCase
{
    // ── seoLink ───────────────────────────────────────────────────────────

    public function testGetSeoLinkReturnsNullByDefault(): void
    {
        $blog = new Blogs();
        self::assertNull($blog->getSeoLink());
    }

    public function testSetAndGetSeoLink(): void
    {
        $blog = new Blogs();
        $blog->setSeoLink('my-blog-post');
        self::assertSame('my-blog-post', $blog->getSeoLink());
    }

    /**
     * The Twig template uses `blog.seoLink ?: blog.id` so that a blog
     * without a slug falls back to its numeric ID.  Verify that the
     * conditional evaluates correctly: a non-empty seoLink wins.
     */
    public function testNonEmptySeoLinkIsTruthy(): void
    {
        $blog = new Blogs();
        $blog->setSeoLink('my-post');
        self::assertNotEmpty($blog->getSeoLink());
    }

    /**
     * When seoLink is null the Twig `?:` falls back to `blog.id`.
     */
    public function testNullSeoLinkIsFalsy(): void
    {
        $blog = new Blogs();
        // getSeoLink() is null → in Twig `null ?: id` uses the id
        self::assertFalse((bool) $blog->getSeoLink());
    }

    // ── previewPicture ────────────────────────────────────────────────────

    public function testGetPreviewPictureReturnsNullByDefault(): void
    {
        $blog = new Blogs();
        self::assertNull($blog->getPreviewPicture());
    }

    public function testSetAndGetPreviewPicture(): void
    {
        $blog = new Blogs();
        $blog->setPreviewPicture('preview.jpg');
        self::assertSame('preview.jpg', $blog->getPreviewPicture());
    }

    /**
     * The Twig `{% if blog.previewPicture %}` guard hides the <img> tag
     * when previewPicture is null – verify the truthiness behaviour.
     */
    public function testNullPreviewPictureIsFalsy(): void
    {
        $blog = new Blogs();
        self::assertFalse((bool) $blog->getPreviewPicture());
    }

    public function testNonEmptyPreviewPictureIsTruthy(): void
    {
        $blog = new Blogs();
        $blog->setPreviewPicture('thumb.png');
        self::assertTrue((bool) $blog->getPreviewPicture());
    }

    // ── getId ─────────────────────────────────────────────────────────────

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $blog = new Blogs();
        self::assertNull($blog->getId());
    }
}
