import { cx } from 'class-variance-authority';
import {
    Color,
    HighlightExtension,
    HorizontalRule,
    Placeholder,
    StarterKit,
    TaskItem,
    TaskList,
    TextStyle,
    TiptapImage,
    TiptapLink,
    TiptapUnderline,
    UploadImagesPlugin,
} from 'novel';
import { CodeBlockExtension } from './code-block-lowlight';

const placeholder = Placeholder.configure({
    placeholder: ({ node }) => {
        if (node.type.name === 'heading') {
            return `Heading ${node.attrs.level}`;
        }
        if (node.type.name === 'taskItem') {
            return 'Item 1';
        }
        if (node.type.name === 'taskList') {
            return '      Task List'; // The whitespace is for accommodating the checkbox before the placeholder
        }
        if (node.type.name === 'codeBlock') {
            return ''; // No placeholder for code blocks
        }
        return "Type '/' for commands...";
    },
    showOnlyWhenEditable: true,
    includeChildren: false,
});

const tiptapLink = TiptapLink.configure({
    HTMLAttributes: {
        class: cx('text-primary underline underline-offset-[3px] hover:text-primary/80 transition-colors cursor-pointer'),
    },
});

const taskList = TaskList.configure({
    HTMLAttributes: {
        class: cx('not-prose pl-0'),
    },
});

const taskItem = TaskItem.configure({
    HTMLAttributes: {
        class: cx('flex items-center gap-2 mb-2'),
    },
    nested: true,
});

const horizontalRule = HorizontalRule.configure({
    HTMLAttributes: {
        class: cx('my-6 border-t border-border'),
    },
});

const starterKit = StarterKit.configure({
    heading: {
        levels: [1, 2, 3],
        HTMLAttributes: {
            class: cx('mb-2'),
        },
    },
    bulletList: {
        HTMLAttributes: {
            class: cx('list-disc list-outside leading-3 -mt-2'),
        },
    },
    orderedList: {
        HTMLAttributes: {
            class: cx('list-decimal list-outside leading-3 -mt-2'),
        },
    },
    listItem: {
        HTMLAttributes: {
            class: cx('leading-normal -mb-2'),
        },
    },
    blockquote: {
        HTMLAttributes: {
            class: cx('border-l-4 border-primary pl-4 italic text-muted-foreground'),
        },
    },
    codeBlock: false, // Disabled - using CodeBlockShiki instead
    code: {
        HTMLAttributes: {
            class: cx('rounded-md bg-muted px-1.5 py-1 font-mono font-medium text-foreground before:content-none after:content-none'),
            spellcheck: 'false',
        },
    },
    horizontalRule: false,
    dropcursor: {
        color: 'color-mix(in oklch, var(--primary) 40%, transparent)',
        width: 4,
    },
    gapcursor: false,
});

const tiptapImage = TiptapImage.extend({
    addProseMirrorPlugins() {
        return [
            UploadImagesPlugin({
                imageClass: cx('opacity-40 rounded border border-stone-200'),
            }),
        ];
    },
}).configure({
    allowBase64: true,
    HTMLAttributes: {
        class: cx('rounded-lg border border-muted'),
    },
});

export const defaultExtensions = [
    starterKit,
    placeholder,
    tiptapLink,
    tiptapImage,
    TiptapUnderline,
    TextStyle,
    Color,
    taskList,
    taskItem,
    horizontalRule,
    HighlightExtension,
    CodeBlockExtension,
];
