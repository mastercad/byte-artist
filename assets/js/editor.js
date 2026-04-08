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
        { cmd: 'link',   icon: 'fa-link',         title: 'Link',           action: (e, btn) => toggleLink(e, btn) ,        active: e => e.isActive('link') },
        { cmd: 'image',  icon: 'fa-image',        title: 'Bild einfügen',  action: (e, btn, ctx) => openImagePicker(ctx),  active: () => false },
        { cmd: 'hr',     icon: 'fa-minus',        title: 'Trennlinie',     action: e => e.chain().focus().setHorizontalRule().run(), active: () => false },
    ]},
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
        // No upload endpoint — insert as data URI
        const reader = new FileReader();
        reader.onload = ev => ctx.editor.chain().focus().setImage({ src: ev.target.result }).run();
        reader.readAsDataURL(file);
        return;
    }
    const reader = new FileReader();
    reader.onload = ev => {
        const body = JSON.stringify({ fileData: ev.target.result, name: file.name });
        fetch(ctx.uploadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
        })
            .then(r => r.json())
            .then(data => {
                if (data.url) {
                    ctx.editor.chain().focus().setImage({ src: data.url }).run();
                }
            })
            .catch(() => {
                // Fallback: insert as data URI
                ctx.editor.chain().focus().setImage({ src: ev.target.result }).run();
            });
    };
    reader.readAsDataURL(file);
}

// ── Build toolbar DOM ──────────────────────────────────────────────────────
function buildToolbar(ctx) {
    const bar = document.createElement('div');
    bar.className = 'tiptap-toolbar';

    for (const group of TOOLBAR) {
        const grpEl = document.createElement('span');
        grpEl.className = 'tiptap-toolbar-group';

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
    const inTable = ctx.editor.isActive('table');
    for (const { el, btn } of ctx.buttons) {
        if (btn.tableOnly) {
            el.style.display = inTable ? '' : 'none';
        }
        if (typeof btn.active === 'function') {
            el.classList.toggle('is-active', btn.active(ctx.editor));
        }
    }
}

// ── Mount one editor on a textarea ────────────────────────────────────────
function mountEditor(textarea) {
    const uploadUrl = textarea.dataset.uploadUrl || '';
    const initialContent = textarea.value || '';

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

    const ctx = { editor: null, buttons: [], uploadUrl };

    const editor = new Editor({
        element: editorHost,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
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

    // Sync initial content to textarea
    textarea.value = editor.getHTML();

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
