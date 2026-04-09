import { describe, it, expect, beforeEach, vi } from 'vitest';
import { notifyUploadStart, notifyUploadEnd } from './editor.js';

// ── helpers ───────────────────────────────────────────────────────────────

function makeCtx(overrides = {}) {
    const badge = document.createElement('span');
    badge.className = 'tiptap-upload-badge';

    const wrapper = document.createElement('div');
    wrapper.className = 'tiptap-wrapper';

    const textarea = document.createElement('textarea');
    document.body.appendChild(textarea);

    return {
        pendingUploads: 0,
        uploadBadge: badge,
        wrapper,
        textarea,
        ...overrides,
    };
}

// ── notifyUploadStart ─────────────────────────────────────────────────────

describe('notifyUploadStart', () => {
    it('increments pendingUploads from 0 to 1', () => {
        const ctx = makeCtx();
        notifyUploadStart(ctx);
        expect(ctx.pendingUploads).toBe(1);
    });

    it('increments pendingUploads cumulatively for concurrent uploads', () => {
        const ctx = makeCtx();
        notifyUploadStart(ctx);
        notifyUploadStart(ctx);
        notifyUploadStart(ctx);
        expect(ctx.pendingUploads).toBe(3);
    });

    it('adds "visible" class to uploadBadge', () => {
        const ctx = makeCtx();
        notifyUploadStart(ctx);
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(true);
    });

    it('adds "tiptap-uploading" class to wrapper', () => {
        const ctx = makeCtx();
        notifyUploadStart(ctx);
        expect(ctx.wrapper.classList.contains('tiptap-uploading')).toBe(true);
    });

    it('dispatches editorUploadStart event on textarea', () => {
        const ctx = makeCtx();
        const handler = vi.fn();
        ctx.textarea.addEventListener('editorUploadStart', handler);
        notifyUploadStart(ctx);
        expect(handler).toHaveBeenCalledOnce();
    });

    it('works without uploadBadge (null-safe)', () => {
        const ctx = makeCtx({ uploadBadge: null });
        expect(() => notifyUploadStart(ctx)).not.toThrow();
        expect(ctx.pendingUploads).toBe(1);
    });

    it('works without wrapper (null-safe)', () => {
        const ctx = makeCtx({ wrapper: null });
        expect(() => notifyUploadStart(ctx)).not.toThrow();
    });

    it('works without textarea (null-safe)', () => {
        const ctx = makeCtx({ textarea: null });
        expect(() => notifyUploadStart(ctx)).not.toThrow();
    });
});

// ── notifyUploadEnd ───────────────────────────────────────────────────────

describe('notifyUploadEnd', () => {
    it('decrements pendingUploads from 1 to 0', () => {
        const ctx = makeCtx({ pendingUploads: 1 });
        notifyUploadEnd(ctx);
        expect(ctx.pendingUploads).toBe(0);
    });

    it('never goes below 0 (guard against double-call)', () => {
        const ctx = makeCtx({ pendingUploads: 0 });
        notifyUploadEnd(ctx);
        expect(ctx.pendingUploads).toBe(0);
    });

    it('removes "visible" class from uploadBadge when counter reaches 0', () => {
        const ctx = makeCtx({ pendingUploads: 1 });
        ctx.uploadBadge.classList.add('visible');
        notifyUploadEnd(ctx);
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(false);
    });

    it('removes "tiptap-uploading" class from wrapper when counter reaches 0', () => {
        const ctx = makeCtx({ pendingUploads: 1 });
        ctx.wrapper.classList.add('tiptap-uploading');
        notifyUploadEnd(ctx);
        expect(ctx.wrapper.classList.contains('tiptap-uploading')).toBe(false);
    });

    it('keeps "visible" class on badge while other uploads are still pending', () => {
        const ctx = makeCtx({ pendingUploads: 2 });
        ctx.uploadBadge.classList.add('visible');
        notifyUploadEnd(ctx);
        expect(ctx.pendingUploads).toBe(1);
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(true);
    });

    it('keeps "tiptap-uploading" on wrapper while other uploads are still pending', () => {
        const ctx = makeCtx({ pendingUploads: 2 });
        ctx.wrapper.classList.add('tiptap-uploading');
        notifyUploadEnd(ctx);
        expect(ctx.wrapper.classList.contains('tiptap-uploading')).toBe(true);
    });

    it('dispatches editorUploadEnd event on textarea', () => {
        const ctx = makeCtx({ pendingUploads: 1 });
        const handler = vi.fn();
        ctx.textarea.addEventListener('editorUploadEnd', handler);
        notifyUploadEnd(ctx);
        expect(handler).toHaveBeenCalledOnce();
    });

    it('removes classes only after last concurrent upload finishes', () => {
        const ctx = makeCtx();
        ctx.uploadBadge.classList.add('visible');
        ctx.wrapper.classList.add('tiptap-uploading');

        notifyUploadStart(ctx);  // pendingUploads = 1
        notifyUploadStart(ctx);  // pendingUploads = 2
        notifyUploadStart(ctx);  // pendingUploads = 3

        notifyUploadEnd(ctx);    // pendingUploads = 2 – still uploading
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(true);

        notifyUploadEnd(ctx);    // pendingUploads = 1 – still uploading
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(true);

        notifyUploadEnd(ctx);    // pendingUploads = 0 – done
        expect(ctx.uploadBadge.classList.contains('visible')).toBe(false);
        expect(ctx.wrapper.classList.contains('tiptap-uploading')).toBe(false);
    });

    it('works without uploadBadge (null-safe)', () => {
        const ctx = makeCtx({ pendingUploads: 1, uploadBadge: null });
        expect(() => notifyUploadEnd(ctx)).not.toThrow();
    });

    it('works without wrapper (null-safe)', () => {
        const ctx = makeCtx({ pendingUploads: 1, wrapper: null });
        expect(() => notifyUploadEnd(ctx)).not.toThrow();
    });

    it('works without textarea (null-safe)', () => {
        const ctx = makeCtx({ pendingUploads: 1, textarea: null });
        expect(() => notifyUploadEnd(ctx)).not.toThrow();
    });
});
