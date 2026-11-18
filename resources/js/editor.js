import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function initRichEditor(root) {
    const htmlField = document.querySelector('[name="content_html"]');
    const jsonField = document.querySelector('[name="content_json"]');
    if (!htmlField || !jsonField) return;

    let initial = '<p></p>';
    const encoded = root.dataset.initialHtml || '';
    if (encoded) {
        try {
            const bytes = Uint8Array.from(atob(encoded), (character) => character.charCodeAt(0));
            initial = new TextDecoder('utf-8').decode(bytes);
        } catch { initial = '<p></p>'; }
    } else if (htmlField.value) initial = htmlField.value;

    const editor = new Editor({
        element: root,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                link: { openOnClick: false },
            }),
            Image.configure({ inline: false, allowBase64: false }),
        ],
        content: initial,
        editorProps: {
            attributes: { class: 'editor-surface', spellcheck: 'true', 'aria-label': 'Nội dung bài viết' },
        },
        onUpdate: ({ editor: instance }) => {
            htmlField.value = instance.getHTML();
            jsonField.value = JSON.stringify(instance.getJSON());
        },
    });

    const toolbar = document.querySelector('[data-editor-toolbar]');
    toolbar?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-command]');
        if (!button) return;
        const command = button.dataset.command;
        const chain = editor.chain().focus();
        const actions = {
            bold: () => chain.toggleBold().run(),
            italic: () => chain.toggleItalic().run(),
            underline: () => chain.toggleUnderline().run(),
            strike: () => chain.toggleStrike().run(),
            h2: () => chain.toggleHeading({ level: 2 }).run(),
            h3: () => chain.toggleHeading({ level: 3 }).run(),
            bullet: () => chain.toggleBulletList().run(),
            ordered: () => chain.toggleOrderedList().run(),
            quote: () => chain.toggleBlockquote().run(),
            hr: () => chain.setHorizontalRule().run(),
            undo: () => chain.undo().run(),
            redo: () => chain.redo().run(),
            link: () => {
                const href = window.prompt('Nhập địa chỉ liên kết (https://...)');
                if (href) chain.setLink({ href }).run();
            },
        };
        actions[command]?.();
    });

    const form = root.closest('form');
    form?.addEventListener('submit', () => {
        htmlField.value = editor.getHTML();
        jsonField.value = JSON.stringify(editor.getJSON());
    });

    const uploadInput = document.querySelector('[data-editor-image-upload]');
    uploadInput?.addEventListener('change', async () => {
        const file = uploadInput.files?.[0];
        if (!file) return;
        const uploadUrl = uploadInput.dataset.uploadUrl;
        const articleId = uploadInput.dataset.articleId;
        if (!articleId) {
            window.alert('Hãy lưu bản nháp trước, sau đó mới tải ảnh nội dung lên.');
            uploadInput.value = '';
            return;
        }
        const body = new FormData();
        body.append('image', file);
        body.append('article_id', articleId);
        try {
            const response = await fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body, credentials: 'same-origin' });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Không thể tải ảnh lên.');
            editor.chain().focus().setImage({ src: result.url, alt: file.name }).run();
        } catch (error) {
            window.alert(error.message);
        } finally {
            uploadInput.value = '';
        }
    });

    const coverInput = document.querySelector('[data-cover-upload]');
    coverInput?.addEventListener('change', async () => {
        const file = coverInput.files?.[0];
        if (!file) return;
        const articleId = coverInput.dataset.articleId;
        if (!articleId) {
            window.alert('Hãy lưu bản nháp trước khi tải ảnh bìa lên.');
            coverInput.value = '';
            return;
        }
        const body = new FormData();
        body.append('image', file);
        body.append('article_id', articleId);
        try {
            const response = await fetch(coverInput.dataset.uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body, credentials: 'same-origin' });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Không thể tải ảnh lên.');
            document.querySelector('[name="cover_media_id"]').value = result.id;
            const preview = document.querySelector('[data-cover-preview]');
            if (preview) { preview.src = result.url; preview.hidden = false; }
        } catch (error) {
            window.alert(error.message);
        } finally {
            coverInput.value = '';
        }
    });
}

document.querySelectorAll('[data-rich-editor]').forEach(initRichEditor);
