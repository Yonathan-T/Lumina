import { Editor, Extension, wrappingInputRule } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Heading from '@tiptap/extension-heading';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import Placeholder from '@tiptap/extension-placeholder';
import Highlight from '@tiptap/extension-highlight';
import Suggestion from '@tiptap/suggestion';
import { PluginKey } from '@tiptap/pm/state';
import { Markdown } from 'tiptap-markdown';

// Define available slash commands matching Lumina brand colors
const getSlashItems = ({ query }) => {
    const items = [
        {
            title: 'Heading 1',
            shortcut: '#',
            description: 'Large title heading',
            category: 'Headings',
            icon: `<span class="text-xs font-bold font-mono text-blue-400">H1</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).setHeading({ level: 1 }).run();
            },
        },
        {
            title: 'Heading 2',
            shortcut: '##',
            description: 'Medium section heading',
            category: 'Headings',
            icon: `<span class="text-xs font-bold font-mono text-blue-400">H2</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).setHeading({ level: 2 }).run();
            },
        },
        {
            title: 'Heading 3',
            shortcut: '###',
            description: 'Small subsection heading',
            category: 'Headings',
            icon: `<span class="text-xs font-bold font-mono text-blue-400">H3</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).setHeading({ level: 3 }).run();
            },
        },
        {
            title: 'To-do Checklist',
            shortcut: '[]',
            description: 'Track tasks with interactive checkboxes',
            category: 'Lists',
            icon: `<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleTaskList().run();
            },
        },
        {
            title: 'Bullet List',
            shortcut: '-',
            description: 'Create an unordered bulleted list',
            category: 'Lists',
            icon: `<svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleBulletList().run();
            },
        },
        {
            title: 'Numbered List',
            shortcut: '1.',
            description: 'Create an ordered sequential list',
            category: 'Lists',
            icon: `<svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h14M7 12h14M7 18h14M3 6h.01M3 12h.01M3 18h.01"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleOrderedList().run();
            },
        },
        {
            title: 'Quote',
            shortcut: '>',
            description: 'Capture a reflection, thought or quote',
            category: 'Blocks',
            icon: `<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleBlockquote().run();
            },
        },
        {
            title: 'Code Block',
            shortcut: '```',
            description: 'Syntax code snippet with monospace styling',
            category: 'Blocks',
            icon: `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleCodeBlock().run();
            },
        },
        {
            title: 'Divider',
            shortcut: '---',
            description: 'Insert a sleek horizontal separator line',
            category: 'Blocks',
            icon: `<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).setHorizontalRule().run();
            },
        },
        {
            title: 'Bold',
            shortcut: 'Ctrl+B',
            description: 'Make text bold and emphasized',
            category: 'Formatting',
            icon: `<span class="text-xs font-bold font-serif text-white">B</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleBold().run();
            },
        },
        {
            title: 'Italic',
            shortcut: 'Ctrl+I',
            description: 'Italicize text with an elegant slant',
            category: 'Formatting',
            icon: `<span class="text-xs italic font-serif text-white">I</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleItalic().run();
            },
        },
        {
            title: 'Strikethrough',
            shortcut: '~~',
            description: 'Cross out text with a horizontal line',
            category: 'Formatting',
            icon: `<span class="text-xs line-through font-serif text-white">S</span>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleStrike().run();
            },
        },
        {
            title: 'Highlight',
            shortcut: '==',
            description: 'Highlight key ideas with subtle glow',
            category: 'Formatting',
            icon: `<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>`,
            command: ({ editor, range }) => {
                editor.chain().focus().deleteRange(range).toggleHighlight().run();
            },
        },
    ];

    if (!query) return items;

    const cleanQuery = query.toLowerCase().trim();
    return items.filter(item =>
        item.title.toLowerCase().includes(cleanQuery) ||
        (item.shortcut && item.shortcut.toLowerCase().includes(cleanQuery)) ||
        item.description.toLowerCase().includes(cleanQuery) ||
        item.category.toLowerCase().includes(cleanQuery)
    );
};

// Self-contained cursor-anchored slash command menu
const createSlashRenderer = () => {
    let menuEl = null;
    let selectedIndex = 0;
    let currentItems = [];
    let currentCommand = null;

    const updateMenuDom = () => {
        if (!menuEl) return;
        menuEl.innerHTML = '';

        if (currentItems.length === 0) {
            menuEl.innerHTML = `
                <div class="px-3 py-2 text-xs text-gray-400 italic">
                    No matching commands
                </div>
            `;
            return;
        }

        const list = document.createElement('div');
        list.className = 'max-h-64 overflow-y-auto space-y-0.5 custom-scrollbar';

        currentItems.forEach((item, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isSelected = index === selectedIndex;
            btn.className = `w-full flex items-center gap-2.5 px-2.5 py-1.5 text-left rounded-lg transition-colors cursor-pointer text-xs ${
                isSelected
                    ? 'bg-blue-300/15 text-white border border-blue-400/30 shadow-sm'
                    : 'text-gray-300 hover:bg-blue-300/15 hover:text-white border border-transparent'
            }`;

            btn.innerHTML = `
                <div class="w-6 h-6 rounded bg-white/5 border border-white/10 flex items-center justify-center shrink-0">
                    ${item.icon}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-gray-100 flex items-center justify-between">
                        <span>${item.title}</span>
                        ${item.shortcut ? `<span class="text-[10px] text-gray-400 font-mono bg-white/5 px-1 rounded">${item.shortcut}</span>` : ''}
                    </div>
                </div>
            `;

            btn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (currentCommand) {
                    currentCommand(item);
                }
            });

            list.appendChild(btn);
        });

        menuEl.appendChild(list);

        const selectedBtn = list.children[selectedIndex];
        if (selectedBtn) {
            selectedBtn.scrollIntoView({ block: 'nearest' });
        }
    };

    const positionMenu = (props) => {
        if (!menuEl || !props) return;

        let top = 0;
        let left = 0;

        // Preferred: get coordinate from ProseMirror cursor
        if (props.editor && props.range) {
            try {
                const coords = props.editor.view.coordsAtPos(props.range.from);
                if (coords && coords.bottom) {
                    top = coords.bottom + 6;
                    left = coords.left;
                }
            } catch (e) {}
        }

        // Fallback: clientRect
        if (!top && props.clientRect) {
            try {
                const rect = typeof props.clientRect === 'function' ? props.clientRect() : props.clientRect;
                if (rect && rect.bottom) {
                    top = rect.bottom + 6;
                    left = rect.left;
                }
            } catch (e) {}
        }

        // Final fallback: active selection
        if (!top) {
            try {
                const sel = window.getSelection();
                if (sel && sel.rangeCount > 0) {
                    const r = sel.getRangeAt(0).getBoundingClientRect();
                    if (r && r.bottom) {
                        top = r.bottom + 6;
                        left = r.left;
                    }
                }
            } catch (e) {}
        }

        if (!top) return;

        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        const menuWidth = 260;
        const menuHeight = 280;

        if (left + menuWidth > viewportWidth - 16) {
            left = viewportWidth - menuWidth - 16;
        }
        if (left < 16) left = 16;

        if (top + menuHeight > viewportHeight - 16 && top > menuHeight + 20) {
            top = top - menuHeight - 12;
        }

        menuEl.style.position = 'fixed';
        menuEl.style.top = `${top}px`;
        menuEl.style.left = `${left}px`;
        menuEl.style.display = 'block';
    };

    return {
        onStart: (props) => {
            currentItems = props.items || [];
            currentCommand = props.command;
            selectedIndex = 0;

            if (menuEl) menuEl.remove();

            menuEl = document.createElement('div');
            menuEl.className = 'w-64 sidebar-gradient bg-gradient-dark border border-white/15 rounded-xl p-1.5 shadow-2xl text-gray-200 z-[99999]';
            document.body.appendChild(menuEl);

            updateMenuDom();
            positionMenu(props);
        },

        onUpdate: (props) => {
            currentItems = props.items || [];
            currentCommand = props.command;
            if (selectedIndex >= currentItems.length) {
                selectedIndex = 0;
            }

            updateMenuDom();
            positionMenu(props);
        },

        onKeyDown: (props) => {
            if (props.event.key === 'Escape') {
                if (menuEl) {
                    menuEl.remove();
                    menuEl = null;
                }
                return true;
            }

            if (props.event.key === 'ArrowUp') {
                selectedIndex = (selectedIndex + currentItems.length - 1) % currentItems.length;
                updateMenuDom();
                return true;
            }

            if (props.event.key === 'ArrowDown') {
                selectedIndex = (selectedIndex + 1) % currentItems.length;
                updateMenuDom();
                return true;
            }

            if (props.event.key === 'Enter') {
                const item = currentItems[selectedIndex];
                if (item && currentCommand) {
                    currentCommand(item);
                    return true;
                }
            }

            return false;
        },

        onExit: () => {
            if (menuEl) {
                menuEl.remove();
                menuEl = null;
            }
        },
    };
};

const SlashCommandsExtension = Extension.create({
    name: 'slashCommands',
    addOptions() {
        return {
            suggestion: {
                char: '/',
                pluginKey: new PluginKey('slashCommands'),
                command: ({ editor, range, props }) => {
                    props.command({ editor, range });
                },
                items: getSlashItems,
                render: createSlashRenderer,
                allow: () => true,
            },
        };
    },
    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                ...this.options.suggestion,
            }),
        ];
    },
});

// Custom Heading that automatically switches back to a normal paragraph when Enter is pressed at the end of the line
const CustomHeading = Heading.extend({
    addKeyboardShortcuts() {
        return {
            ...this.parent?.(),
            Enter: ({ editor }) => {
                const { state } = editor;
                const { selection } = state;
                const { $from, empty } = selection;

                if (!empty || $from.parent.type.name !== 'heading') {
                    return false;
                }

                const isAtEnd = $from.parentOffset === $from.parent.content.size;
                const isEmpty = $from.parent.content.size === 0;

                // If on an empty heading, turn it back into a normal paragraph
                if (isEmpty) {
                    return editor.commands.setParagraph();
                }

                // If cursor is at the end of the heading line, split and convert the new line to paragraph
                if (isAtEnd) {
                    return editor.chain().splitBlock().setParagraph().run();
                }

                return false;
            },
        };
    },
});

// Custom TaskItem with robust input rules for "[]", "[x]", "[] ", "[ ] ", "[x] ", and "- [ ] "
const CustomTaskItem = TaskItem.extend({
    addInputRules() {
        return [
            // Instant trigger when user finishes typing "[]" at start of line
            wrappingInputRule({
                find: /^\s*\[\]$/,
                type: this.type,
                getAttributes: () => ({ checked: false }),
            }),
            // Instant trigger when user finishes typing "[x]" at start of line
            wrappingInputRule({
                find: /^\s*\[x\]$/,
                type: this.type,
                getAttributes: () => ({ checked: true }),
            }),
            // Standard trigger when typing "[] ", "[ ] ", "[x] ", "- [ ] ", "- [] " followed by space
            wrappingInputRule({
                find: /^\s*(- )?(\[([ |x])?\]|\[\])\s$/,
                type: this.type,
                getAttributes: (match) => ({
                    checked: match[0].includes('[x]'),
                }),
            }),
        ];
    },
});

/**
 * Alpine component factory for Memo-Mate rich canvas editor
 */
export function initMemoEditor({ initialContent = '', placeholder = "Type '/' for commands or start writing...", wireModel = null, onChange = null } = {}) {
    return {
        editor: null,
        wordCount: 0,
        charCount: 0,
        activeStates: {
            bold: false,
            italic: false,
            strike: false,
            heading1: false,
            heading2: false,
            heading3: false,
            taskList: false,
            bulletList: false,
            orderedList: false,
            blockquote: false,
            codeBlock: false,
            highlight: false,
        },

        syncMarkdown(markdown) {
            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = markdown;
            }
            if (wireModel && this.$wire) {
                this.$wire[wireModel] = markdown;
            }
            if (typeof onChange === 'function') {
                onChange(markdown);
            }
        },

        flushSync() {
            if (this.editor) {
                const md = this.editor.storage.markdown.getMarkdown();
                this.syncMarkdown(md);
            }
        },

        init() {
            const editorEl = this.$refs.editorContainer;
            if (!editorEl) return;

            // Expose helpers on DOM element for instant programmatic access
            this.$el.__memoGetMarkdown = () => this.getMarkdown();
            this.$el.__memoFlushSync = () => this.flushSync();

            this.editor = new Editor({
                element: editorEl,
                extensions: [
                    StarterKit.configure({
                        heading: false,
                        codeBlock: {
                            HTMLAttributes: {
                                class: 'memo-code-block font-mono bg-black/70 border border-white/10 rounded-xl p-3 my-2 text-emerald-400 text-sm overflow-x-auto shadow-inner',
                            },
                        },
                        blockquote: {
                            HTMLAttributes: {
                                class: 'memo-blockquote border-l-2 border-blue-400/80 bg-white/[0.03] pl-3.5 pr-3 py-1.5 my-2 rounded-r-lg italic text-gray-300',
                            },
                        },
                        bulletList: {
                            HTMLAttributes: {
                                class: 'memo-bullet-list list-disc pl-5 my-1.5 space-y-0.5 text-gray-200',
                            },
                        },
                        orderedList: {
                            HTMLAttributes: {
                                class: 'memo-ordered-list list-decimal pl-5 my-1.5 space-y-0.5 text-gray-200',
                            },
                        },
                        paragraph: {
                            HTMLAttributes: {
                                class: 'memo-paragraph leading-relaxed text-gray-200 text-inherit',
                            },
                        },
                        horizontalRule: {
                            HTMLAttributes: {
                                class: 'memo-hr my-3 border-white/10',
                            },
                        },
                    }),
                    CustomHeading.configure({
                        levels: [1, 2, 3],
                    }),
                    TaskList.configure({
                        HTMLAttributes: {
                            class: 'memo-task-list list-none p-0 my-1.5 space-y-1',
                        },
                    }),
                    CustomTaskItem.configure({
                        nested: true,
                        HTMLAttributes: {
                            class: 'memo-task-item',
                        },
                    }),
                    Highlight.configure({
                        multicolor: false,
                        HTMLAttributes: {
                            class: 'memo-highlight bg-blue-400/20 text-blue-200 px-1 py-0.5 rounded border border-blue-400/30',
                        },
                    }),
                    Placeholder.configure({
                        placeholder: placeholder,
                        emptyNodeClass: 'is-empty',
                    }),
                    Markdown.configure({
                        html: false,
                        tightLists: true,
                        bulletListMarker: '-',
                        linkify: true,
                    }),
                    SlashCommandsExtension,
                ],
                content: initialContent || '',
                editorProps: {
                    attributes: {
                        class: 'tiptap ProseMirror focus:outline-none min-h-[300px] w-full text-inherit leading-relaxed text-gray-100',
                    },
                },
                onUpdate: ({ editor }) => {
                    this.updateStats();
                    this.updateActiveStates();
                    const markdown = editor.storage.markdown.getMarkdown();
                    this.syncMarkdown(markdown);
                },
                onTransaction: () => {
                    this.updateActiveStates();
                },
                onSelectionUpdate: () => {
                    this.updateActiveStates();
                },
            });

            // Watch for external Livewire model resets safely within Alpine context
            if (wireModel && typeof this.$wire !== 'undefined' && this.$wire && typeof this.$wire.$watch === 'function') {
                this.$wire.$watch(wireModel, (newVal) => {
                    // Only clear if Livewire reset content to empty AND the editor is not actively focused by the user
                    if (this.editor && newVal === '' && !this.editor.isFocused) {
                        this.editor.commands.clearContent();
                    }
                });
            }

            this.updateStats();
            this.updateActiveStates();
        },

        updateStats() {
            if (!this.editor) return;
            const text = this.editor.getText().trim();
            this.charCount = text.length;
            this.wordCount = text ? text.split(/\s+/).filter(Boolean).length : 0;
        },

        updateActiveStates() {
            if (!this.editor) return;
            this.activeStates = {
                bold: this.editor.isActive('bold'),
                italic: this.editor.isActive('italic'),
                strike: this.editor.isActive('strike'),
                heading1: this.editor.isActive('heading', { level: 1 }),
                heading2: this.editor.isActive('heading', { level: 2 }),
                heading3: this.editor.isActive('heading', { level: 3 }),
                taskList: this.editor.isActive('taskList'),
                bulletList: this.editor.isActive('bulletList'),
                orderedList: this.editor.isActive('orderedList'),
                blockquote: this.editor.isActive('blockquote'),
                codeBlock: this.editor.isActive('codeBlock'),
                highlight: this.editor.isActive('highlight'),
            };
        },

        toggleBold() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleBold().run();
            this.updateActiveStates();
        },

        toggleItalic() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleItalic().run();
            this.updateActiveStates();
        },

        toggleStrike() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleStrike().run();
            this.updateActiveStates();
        },

        toggleHeading(level) {
            if (!this.editor) return;
            this.editor.chain().focus().toggleHeading({ level }).run();
            this.updateActiveStates();
        },

        toggleTaskList() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleTaskList().run();
            this.updateActiveStates();
        },

        toggleBulletList() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleBulletList().run();
            this.updateActiveStates();
        },

        toggleOrderedList() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleOrderedList().run();
            this.updateActiveStates();
        },

        toggleBlockquote() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleBlockquote().run();
            this.updateActiveStates();
        },

        toggleCodeBlock() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleCodeBlock().run();
            this.updateActiveStates();
        },

        toggleHighlight() {
            if (!this.editor) return;
            this.editor.chain().focus().toggleHighlight().run();
            this.updateActiveStates();
        },

        insertDivider() {
            if (!this.editor) return;
            this.editor.chain().focus().setHorizontalRule().run();
            this.updateActiveStates();
        },

        getMarkdown() {
            return this.editor ? this.editor.storage.markdown.getMarkdown() : '';
        },

        destroy() {
            if (this.editor) {
                try {
                    this.editor.destroy();
                } catch (e) {
                    // Ignore DOM cleanup if node was already unmounted
                }
                this.editor = null;
            }
        },
    };
}

// Make globally accessible for Alpine.js
window.initMemoEditor = initMemoEditor;
