import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Table, TableRow, TableCell, TableHeader } from '@tiptap/extension-table';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';

import '../styles/editor.css';

// ── Toolbar definition ─────────────────────────────────────────────────────
const TOOLBAR = [
    { group: 'history', buttons: [
        { cmd: 'undo',   icon: 'fa-undo',   title: 'Rückgängig (Ctrl+Z)', action: e => e.chain().focus().undo().run(), active: () => false },
        { cmd: 'redo',   icon: 'fa-redo',   title: 'Wiederholen (Ctrl+Y)', action: e => e.chain().focus().redo().run(), active: () => false },
    ]},
    { group: 'inline', buttons: [
        { cmd: 'bold',   icon: 'fa-bold',   title: 'Fett (Ctrl+B)',   action: e => e.chain().focus().toggleBold().run(),   active: e => e.isActive('bold') },
        { cmd: 'italic', icon: 'fa-italic', title: 'Kursiv (Ctrl+I)', action: e => e.chain().focus().toggleItalic().run(), active: e => e.isActive('italic') },
        { cmd: 'strike', icon: 'fa-strikethrough', title: 'Durchgestrichen', action: e => e.chain().focus().toggleStrike().run(), active: e => e.isActive('strike') },
        { cmd: 'code',   icon: 'fa-code',   title: 'Inline-Code', action: e => e.chain().focus().toggleCode().run(), active: e => e.isActive('code') },
    ]},
    { group: 'heading', buttons: [
        { cmd: 'h2', label: 'H2', title: 'Überschrift 2', action: e => e.chain().focus().toggleHeading({level: 2}).run(), active: e => e.isActive('heading', {level: 2}) },
        { cmd: 'h3', label: 'H3', title: 'Überschrift 3', action: e => e.chain().focus().toggleHeading({level: 3}).run(), active: e => e.isActive('heading', {level: 3}) },
    ]},
    { group: 'align', buttons: [
        { cmd: 'alignLeft',   icon: 'fa-align-left',    title: 'Linksbündig',  action: e => e.chain().focus().setTextAlign('left').run(),    active: e => e.isActive({textAlign: 'left'}) },
        { cmd: 'alignCenter', icon: 'fa-align-center',  title: 'Zentriert',    action: e => e.chain().focus().setTextAlign('center').run(),  active: e => e.isActive({textAlign: 'center'}) },
        { cmd: 'alignRight',  icon: 'fa-align-right',   title: 'Rechtsbündig', action: e => e.chain().focus().setTextAlign('right').run(),   active: e => e.isActive({textAlign: 'right'}) },
    ]},
    { group: 'lists', buttons: [
        { cmd: 'bulletList',  icon: 'fa-list-ul',       title: 'Aufzählung',     action: e => e.chain().focus().toggleBulletList().run(),  active: e => e.isActive('bulletList') },
        { cmd: 'orderedList', icon: 'fa-list-ol',       title: 'Nummerierung',   action: e => e.chain().focus().toggleOrderedList().run(), active: e => e.isActive('orderedList') },
        { cmd: 'taskList',    icon: 'fa-tasks',         title: 'Aufgabenliste',  action: e => e.chain().focus().toggleTaskList().run(),     active: e => e.isActive('taskList') },
        { cmd: 'blockquote',  icon: 'fa-quote-right',   title: 'Zitat',          action: e => e.chain().focus().toggleBlockquote().run(),   active: e => e.isActive('blockquote') },
        { cmd: 'codeBlock',   icon: 'fa-file-code',     title: 'Code-Block',     action: e => e.chain().focus().toggleCodeBlock().run(),    active: e => e.isActive('codeBlock') },
    ]},
    { group: 'table', buttons: [
        { cmd: 'insertTable', icon: 'fa-table', title: 'Tabelle einfügen', action: e => e.chain().focus().insertTable({rows: 3, cols: 3, withHeaderRow: true}).run(), active: () => false },
        { cmd: 'addColAfter',  icon: 'fa-columns',     title: 'Spalte rechts einfügen', action: e => e.chain().focus().addColumnAfter().run(),  active: () => false, tableOnly: true },
        { cmd: 'delCol',       icon: 'fa-minus',       title: 'Spalte löschen',         action: e => e.chain().focus().deleteColumn().run(),     active: () => false, tableOnly: true },
        { cmd: 'addRowAfter',  icon: 'fa-plus',        title: 'Zeile unten einfügen',   action: e => e.chain().focus().addRowAfter().run(),      active: () => false, tableOnly: true },
        { cmd: 'delRow',       icon: 'fa-trash-alt',   title: 'Zeile löschen',          action: e => e.chain().focus().deleteRow().run(),        active: () => false, tableOnly: true },
        { cmd: 'mergeOrSplit', icon: 'fa-object-group',title: 'Zellen zusammenführen / trennen', action: e => e.chain().focus().mergeOrSplit().run(), active: () => false, tableOnly: true },
        { cmd: 'deleteTable',  icon: 'fa-times',       title: 'Tabelle löschen',        action: e => e.chain().focus().deleteTable().run(),      active: () => false, tableOnly: true },
    ]},
    { group: 'insert', buttons: [
        { cmd: 'link',   icon: 'fa-link',         title: 'Link',           action: (e, btn) => toggleLink(e, btn),        active: e => e.isActive('link') },
        { cmd: 'image',  icon: 'fa-image',        title: 'Bild einfügen',  action: (e, btn, ctx) => openImagePicker(ctx), active: () => false },
        { cmd: 'hr',     icon: 'fa-minus',        title: 'Trennlinie',     action: e => e.chain().focus().setHorizontalRule().run(), active: () => false },
    ]},
    { group: 'codeLanguage', select: true },
];

const CODE_LANGUAGES = [
    { value: '',           label: '— Sprache —' },
    { value: 'bash',       label: 'Bash / Shell' },
    { value: 'c',          label: 'C' },
    { value: 'cpp',        label: 'C++' },
    { value: 'css',        label: 'CSS' },
    { value: 'diff',       label: 'Diff' },
    { value: 'dockerfile', label: 'Dockerfile' },
    { value: 'go',         label: 'Go' },
    { value: 'html',       label: 'HTML' },
    { value: 'java',       label: 'Java' },
    { value: 'javascript', label: 'JavaScript' },
    { value: 'json',       label: 'JSON' },
    { value: 'markdown',   label: 'Markdown' },
    { value: 'php',        label: 'PHP' },
    { value: 'python',     label: 'Python' },
    { value: 'rust',       label: 'Rust' },
    { value: 'sql',        label: 'SQL' },
    { value: 'typescript', label: 'TypeScript' },
    { value: 'xml',        label: 'XML' },
    { value: 'yaml',       label: 'YAML' },
];

// ── Link dialog ────────────────────────────────────────────────────────────
function toggleLink(editor, btn) {
    if (editor.isActive('link')) {
        editor.chain().focus().unsetLink().run();
        return;
    }
    const url = window.prompt('URL eingeben:', 'https://');
    if (url) {
        editor.chain().focus().setLink({ href: url, target: '_blank' }).run();
    }
}

// ── Upload lifecycle notifiers ───────────────────────────────────────────────
export function notifyUploadStart(ctx) {
    ctx.pendingUploads = (ctx.pendingUploads || 0) + 1;
    if (ctx.uploadBadge) ctx.uploadBadge.classList.add('visible');
    if (ctx.wrapper)     ctx.wrapper.classList.add('tiptap-uploading');
    if (ctx.textarea)    ctx.textarea.dispatchEvent(new CustomEvent('editorUploadStart', { bubbles: true }));
}

export function notifyUploadEnd(ctx) {
    ctx.pendingUploads = Math.max(0, (ctx.pendingUploads || 0) - 1);
    if (ctx.pendingUploads === 0) {
        if (ctx.uploadBadge) ctx.uploadBadge.classList.remove('visible');
        if (ctx.wrapper)     ctx.wrapper.classList.remove('tiptap-uploading');
    }
    if (ctx.textarea)    ctx.textarea.dispatchEvent(new CustomEvent('editorUploadEnd', { bubbles: true }));
}

// ── Image: file picker → upload → insert ──────────────────────────────────
function openImagePicker(ctx) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = () => {
        if (input.files[0]) uploadAndInsertImage(ctx, input.files[0]);
    };
    input.click();
}

function uploadAndInsertImage(ctx, file) {
    if (!ctx.uploadUrl) {
        const reader = new FileReader();
        reader.onload = ev => ctx.editor.chain().focus().setImage({ src: ev.target.result }).run();
        reader.readAsDataURL(file);
        return;
    }
    const reader = new FileReader();
    reader.onload = ev => {
        notifyUploadStart(ctx);
        const body = JSON.stringify({ fileData: ev.target.result, name: file.name });
        fetch(ctx.uploadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
        })
            .then(r => r.json())
            .then(data => {
                notifyUploadEnd(ctx);
                if (data.url) {
                    ctx.editor.chain().focus().setImage({ src: data.url }).run();
                }
            })
            .catch(() => {
                notifyUploadEnd(ctx);
                ctx.editor.chain().focus().setImage({ src: ev.target.result }).run();
            });
    };
    reader.readAsDataURL(file);
}

// ── Image: replace existing node ───────────────────────────────────────────
function uploadAndReplaceImage(ctx, file, nodePos) {
    function applyUrl(url) {
        ctx.editor.commands.setNodeSelection(nodePos);
        ctx.editor.chain().focus().updateAttributes('image', { src: url }).run();
    }

    if (!ctx.uploadUrl) {
        const reader = new FileReader();
        reader.onload = ev => applyUrl(ev.target.result);
        reader.readAsDataURL(file);
        return;
    }
    const reader = new FileReader();
    reader.onload = ev => {
        notifyUploadStart(ctx);
        const body = JSON.stringify({ fileData: ev.target.result, name: file.name });
        fetch(ctx.uploadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
        })
            .then(r => r.json())
            .then(data => {
                notifyUploadEnd(ctx);
                applyUrl(data.url || ev.target.result);
            })
            .catch(() => {
                notifyUploadEnd(ctx);
                applyUrl(ev.target.result);
            });
    };
    reader.readAsDataURL(file);
}

// ── Image edit modal ────────────────────────────────────────────────────────

function buildImageModal(ctx) {
    // Backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'tt-img-modal-backdrop';

    // Dialog
    const dialog = document.createElement('div');
    dialog.className = 'tt-img-modal';
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-label', 'Bild bearbeiten');

    // Header
    const header = document.createElement('div');
    header.className = 'tt-img-modal-header';
    header.innerHTML = '<span class="tt-img-modal-title"><i class="fas fa-image"></i> Bild bearbeiten</span>';
    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'tt-img-modal-close';
    closeBtn.title = 'Schließen (Esc)';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    header.appendChild(closeBtn);

    // Drop zone
    const dropZone = document.createElement('div');
    dropZone.className = 'tt-img-modal-dropzone';
    dropZone.innerHTML = `
        <div class="tt-img-modal-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <div class="tt-img-modal-dropzone-text">Bild hierher ziehen</div>
        <div class="tt-img-modal-dropzone-sub">oder</div>
    `;
    const pickBtn = document.createElement('button');
    pickBtn.type = 'button';
    pickBtn.className = 'tt-img-modal-pick-btn';
    pickBtn.innerHTML = '<i class="fas fa-folder-open"></i> Datei auswählen';
    dropZone.appendChild(pickBtn);

    // URL row
    const urlRow = document.createElement('div');
    urlRow.className = 'tt-img-modal-url-row';
    const urlLabel = document.createElement('label');
    urlLabel.className = 'tt-img-modal-url-label';
    urlLabel.textContent = 'oder Bild-URL';
    const urlInput = document.createElement('input');
    urlInput.type = 'url';
    urlInput.className = 'tt-img-modal-url-input';
    urlInput.placeholder = 'https://…';
    urlInput.spellcheck = false;
    urlInput.autocomplete = 'off';
    const urlApplyBtn = document.createElement('button');
    urlApplyBtn.type = 'button';
    urlApplyBtn.className = 'tt-img-modal-url-apply';
    urlApplyBtn.innerHTML = '<i class="fas fa-check"></i> Übernehmen';
    urlRow.append(urlLabel, urlInput, urlApplyBtn);

    // Footer actions
    const footer = document.createElement('div');
    footer.className = 'tt-img-modal-footer';
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'tt-img-modal-remove-btn';
    removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Bild entfernen';
    footer.appendChild(removeBtn);

    dialog.append(header, dropZone, urlRow, footer);
    backdrop.appendChild(dialog);
    document.body.appendChild(backdrop);

    // ── Event wiring ──────────────────────────────────────────────────────

    const close = () => hideImageModal(ctx);

    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', ev => { if (ev.target === backdrop) close(); });

    document.addEventListener('keydown', ev => {
        if (ev.key === 'Escape' && backdrop.classList.contains('tt-img-modal-backdrop--open')) {
            close();
        }
    });

    // File picker trigger
    const triggerFilePicker = () => {
        const savedPos = ctx._editPos;
        if (savedPos == null) return;
        const fi = document.createElement('input');
        fi.type = 'file';
        fi.accept = 'image/*';
        fi.onchange = () => {
            if (fi.files[0]) {
                close();
                uploadAndReplaceImage(ctx, fi.files[0], savedPos);
            }
        };
        fi.click();
    };

    pickBtn.addEventListener('click', triggerFilePicker);

    // Drag & drop on the drop zone
    dropZone.addEventListener('dragover', ev => {
        ev.preventDefault();
        dropZone.classList.add('tt-img-modal-dropzone--over');
    });
    dropZone.addEventListener('dragleave', ev => {
        if (!dropZone.contains(ev.relatedTarget)) {
            dropZone.classList.remove('tt-img-modal-dropzone--over');
        }
    });
    dropZone.addEventListener('drop', ev => {
        ev.preventDefault();
        dropZone.classList.remove('tt-img-modal-dropzone--over');
        const files = Array.from(ev.dataTransfer ? ev.dataTransfer.files : []).filter(f => f.type.startsWith('image/'));
        if (files[0]) {
            const savedPos = ctx._editPos;
            close();
            uploadAndReplaceImage(ctx, files[0], savedPos);
        }
    });

    // URL apply
    const applyUrl = () => {
        const src = urlInput.value.trim();
        if (src && ctx._editPos != null) {
            ctx.editor.commands.setNodeSelection(ctx._editPos);
            ctx.editor.chain().focus().updateAttributes('image', { src }).run();
        }
        close();
    };
    urlApplyBtn.addEventListener('click', applyUrl);
    urlInput.addEventListener('keydown', ev => {
        if (ev.key === 'Enter') { ev.preventDefault(); applyUrl(); }
    });

    // Remove image
    removeBtn.addEventListener('click', () => {
        if (ctx._editPos != null) {
            ctx.editor.commands.setNodeSelection(ctx._editPos);
            ctx.editor.chain().focus().deleteSelection().run();
        }
        close();
    });

    // Store the urlInput reference for pre-population
    backdrop._urlInput = urlInput;
    return backdrop;
}

function showImageModal(ctx, imgEl, nodePos) {
    ctx._editPos = nodePos;
    const urlInput = ctx.imgPopover._urlInput;
    urlInput.value = imgEl.getAttribute('src') || '';
    ctx.imgPopover.classList.add('tt-img-modal-backdrop--open');
    // Focus the drop zone visually, URL input on next tick
    setTimeout(() => urlInput.select(), 50);
}

function hideImageModal(ctx) {
    if (ctx.imgPopover) ctx.imgPopover.classList.remove('tt-img-modal-backdrop--open');
}

// Keep the old names as thin aliases so call sites below don't need changing
function hideImagePopover(ctx) { hideImageModal(ctx); }

// ── Build toolbar DOM ──────────────────────────────────────────────────────
function buildToolbar(ctx) {
    const bar = document.createElement('div');
    bar.className = 'tiptap-toolbar';

    for (const group of TOOLBAR) {
        const grpEl = document.createElement('span');
        grpEl.className = 'tiptap-toolbar-group';

        if (group.select) {
            // Code language selector
            const sel = document.createElement('select');
            sel.className = 'tiptap-code-lang-select';
            sel.title = 'Code-Sprache';
            sel.style.display = 'none';
            for (const opt of CODE_LANGUAGES) {
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.label;
                sel.appendChild(o);
            }
            sel.addEventListener('change', () => {
                const lang = sel.value;
                ctx.editor.chain().focus().setCodeBlock({ language: lang || null }).run();
            });
            ctx.codeLangSelect = sel;
            grpEl.appendChild(sel);
            bar.appendChild(grpEl);
            continue;
        }

        for (const btn of group.buttons) {
            const el = document.createElement('button');
            el.type = 'button';
            el.title = btn.title;
            el.dataset.cmd = btn.cmd;
            if (btn.tableOnly) el.dataset.tableOnly = '1';

            if (btn.icon) {
                el.innerHTML = `<i class="fas ${btn.icon}"></i>`;
            } else if (btn.label) {
                el.textContent = btn.label;
            }

            el.addEventListener('click', () => btn.action(ctx.editor, el, ctx));
            ctx.buttons.push({ el, btn });
            grpEl.appendChild(el);
        }
        bar.appendChild(grpEl);
    }
    return bar;
}

// ── Update toolbar active / table-only visibility ─────────────────────────
function updateToolbar(ctx) {
    const inTable    = ctx.editor.isActive('table');
    const inCodeBlock = ctx.editor.isActive('codeBlock');
    for (const { el, btn } of ctx.buttons) {
        if (btn.tableOnly) {
            el.style.display = inTable ? '' : 'none';
        }
        if (typeof btn.active === 'function') {
            el.classList.toggle('is-active', btn.active(ctx.editor));
        }
    }
    if (ctx.codeLangSelect) {
        ctx.codeLangSelect.style.display = inCodeBlock ? '' : 'none';
        if (inCodeBlock) {
            const attrs = ctx.editor.getAttributes('codeBlock');
            ctx.codeLangSelect.value = attrs.language || '';
        }
    }
}

// ── Mount one editor on a textarea ────────────────────────────────────────
function sanitizeEditorHtml(html) {
    if (!html) return html;
    // Strip <blockquote> wrappers around <pre> (code blocks must not live inside blockquotes)
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    tmp.querySelectorAll('blockquote > pre').forEach(pre => pre.parentNode.replaceWith(pre));
    return tmp.innerHTML;
}

function mountEditor(textarea) {
    const uploadUrl      = textarea.dataset.uploadUrl || '';
    const initialContent = sanitizeEditorHtml(textarea.value || '');

    // Wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'tiptap-wrapper';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(textarea);
    textarea.style.display = 'none';

    // Toolbar placeholder (filled after editor exists)
    const toolbarPlaceholder = document.createElement('div');
    wrapper.appendChild(toolbarPlaceholder);

    // Editor host
    const editorHost = document.createElement('div');
    editorHost.className = 'tiptap-editor-host';
    wrapper.appendChild(editorHost);

    const ctx = { editor: null, buttons: [], codeLangSelect: null, uploadUrl, imgPopover: null, _editPos: null, pendingUploads: 0, textarea, wrapper, uploadBadge: null };

    const editor = new Editor({
        element: editorHost,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
                link: false,
            }),
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            Table.configure({ resizable: false }),
            TableRow,
            TableHeader,
            TableCell,
            TaskList,
            TaskItem.configure({ nested: true }),
            Link.configure({ openOnClick: false, autolink: true }),
            Image.configure({ inline: false }),
        ],
        content: initialContent,
        autofocus: false,
        onUpdate: ({ editor: e }) => {
            textarea.value = e.getHTML();
            textarea.dispatchEvent(new Event('change', { bubbles: true }));
        },
        onSelectionUpdate: ({ editor: e }) => {
            ctx.editor = e;
            updateToolbar(ctx);
        },
        onTransaction: ({ editor: e }) => {
            ctx.editor = e;
            updateToolbar(ctx);
        },
    });

    ctx.editor = editor;

    // Build and insert toolbar
    const toolbar = buildToolbar(ctx);
    toolbarPlaceholder.replaceWith(toolbar);

    // Upload status badge (shown during active uploads)
    const uploadBadge = document.createElement('span');
    uploadBadge.className = 'tiptap-upload-badge';
    uploadBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Bild wird hochgeladen…';
    toolbar.appendChild(uploadBadge);
    ctx.uploadBadge = uploadBadge;

    // Sync initial content to textarea
    textarea.value = editor.getHTML();

    // ── Image edit modal ─────────────────────────────────────────────────
    ctx.imgPopover = buildImageModal(ctx);

    // Click on an existing image → open the edit modal
    editorHost.addEventListener('click', ev => {
        if (ev.target.tagName === 'IMG') {
            // Let ProseMirror process the click and create the NodeSelection first
            setTimeout(() => {
                const sel = ctx.editor.state.selection;
                if (sel && sel.node && sel.node.type.name === 'image') {
                    showImageModal(ctx, ev.target, sel.from);
                }
            }, 0);
        } else {
            hideImageModal(ctx);
        }
    });

    // Dismiss modal when clicking outside both the editor and the modal
    document.addEventListener('click', ev => {
        if (
            ctx.imgPopover.classList.contains('tt-img-modal-backdrop--open') &&
            !ctx.imgPopover.querySelector('.tt-img-modal').contains(ev.target) &&
            !editorHost.contains(ev.target)
        ) {
            hideImageModal(ctx);
        }
    });

    // Dismiss modal on scroll so it stays in sync
    document.addEventListener('scroll', () => hideImageModal(ctx), { passive: true, capture: true });

    // ── Drag & drop images into the editor ──────────────────────────────
    editorHost.addEventListener('drop', ev => {
        const files = Array.from(ev.dataTransfer ? ev.dataTransfer.files : []).filter(f => f.type.startsWith('image/'));
        if (files.length) {
            ev.preventDefault();
            files.forEach(f => uploadAndInsertImage(ctx, f));
        }
    });

    // ── Paste images ────────────────────────────────────────────────────
    editorHost.addEventListener('paste', ev => {
        const items = Array.from(ev.clipboardData ? ev.clipboardData.items : []);
        const imageItems = items.filter(i => i.type.startsWith('image/'));
        if (imageItems.length) {
            ev.preventDefault();
            imageItems.forEach(item => {
                const file = item.getAsFile();
                if (file) uploadAndInsertImage(ctx, file);
            });
        }
    });

    return editor;
}

// ── Boot ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea.tiptap-source').forEach(mountEditor);
});
