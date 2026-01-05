'use client';

import {
    EditorBubble,
    EditorCommand,
    EditorCommandEmpty,
    EditorCommandItem,
    EditorCommandList,
    EditorContent,
    EditorInstance,
    EditorRoot,
    handleCommandNavigation,
    handleImageDrop,
    handleImagePaste,
    ImageResizer,
    JSONContent,
} from 'novel';
import GlobalDragHandle from 'tiptap-extension-global-drag-handle';

import { useEffect, useState } from 'react';
import { useDebouncedCallback } from 'use-debounce';
import { defaultExtensions } from './extensions';
import { uploadFn } from './image-upload';
import { ColorSelector } from './selectors/color-selector';
import { LinkSelector } from './selectors/link-selector';
import { NodeSelector } from './selectors/node-selector';
import { TextButtons } from './selectors/text-buttons-selector';
import { slashCommand, suggestionItems } from './slash-command';

// import styles
import '../../../../css/text-editor.css';

interface RichTextEditorProps {
    initialContent?: JSONContent;
    onUpdate?: (content: JSONContent) => void;
    name?: string;
    className?: string;
}

const RichTextEditor = ({ initialContent, onUpdate, name, className }: RichTextEditorProps) => {
    // Track the content for the hidden input (form submission)
    const [content, setContent] = useState<undefined | JSONContent>(initialContent);
    const [status, setStatus] = useState<'Saved' | 'Unsaved'>('Saved');
    const [openNode, setOpenNode] = useState(false);
    const [openLink, setOpenLink] = useState(false);
    const [openColor, setOpenColor] = useState(false);

    // Generate a stable key for the editor based on initial content
    // This forces EditorContent to remount when initialContent changes
    const [editorKey, setEditorKey] = useState(() => 
        initialContent ? JSON.stringify(initialContent).slice(0, 100) : 'empty'
    );

    const handleDebouncedUpdate = useDebouncedCallback(async (editor: EditorInstance) => {
        const json = editor.getJSON();
        setContent(json);
        setStatus('Saved');
        onUpdate?.(json);
    }, 500);

    // Sync content and editor key when initialContent changes from parent
    useEffect(() => {
        setContent(initialContent);
        setEditorKey(initialContent ? JSON.stringify(initialContent).slice(0, 100) : 'empty');
    }, [initialContent]);

    const extensions = [
        ...defaultExtensions,
        slashCommand,
        GlobalDragHandle.configure({
            dragHandleWidth: 20,
            scrollTreshold: 100,
        }),
    ];

    return (
        <EditorRoot>
            {name && (
                <input
                    type="hidden"
                    name={name}
                    value={content ? JSON.stringify(content) : ''}
                />
            )}
            <EditorContent
                key={editorKey}
                initialContent={initialContent}
                extensions={extensions}
                className={className || 'relative min-h-18 w-full max-w-5xl rounded-xl border border-input bg-background px-6 py-4 shadow-sm transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50'}
                editorProps={{
                    handleDOMEvents: {
                        keydown: (_view, event) => {
                            // Stop propagation of editor keyboard shortcuts to prevent global handlers
                            const isEditorShortcut =
                                (event.ctrlKey || event.metaKey) &&
                                ['b', 'i', 'u', 'k', 'z', 'y', 'shift+z'].includes(
                                    event.shiftKey ? `shift+${event.key.toLowerCase()}` : event.key.toLowerCase(),
                                );

                            if (isEditorShortcut) {
                                event.stopPropagation();
                            }

                            return handleCommandNavigation(event);
                        },
                    },
                    handlePaste: (view, event) => handleImagePaste(view, event, uploadFn),
                    handleDrop: (view, event, _slice, moved) => handleImageDrop(view, event, moved, uploadFn),
                    attributes: {
                        class: 'prose prose-lg dark:prose-invert h-full prose-headings:font-semibold focus:outline-none max-w-full prose-headings:text-foreground prose-p:text-foreground prose-strong:text-foreground prose-em:text-foreground prose-code:text-foreground prose-pre:bg-muted prose-pre:text-foreground prose-blockquote:text-muted-foreground prose-li:text-foreground prose-hr:border-border',
                    },
                }}
                onUpdate={({ editor }) => {
                    handleDebouncedUpdate(editor);
                    setStatus('Unsaved');
                }}
                slotAfter={<ImageResizer />}
            >
                <EditorCommand className="z-50 h-auto max-h-[330px] w-72 overflow-y-auto rounded-xl border border-border bg-popover px-1 py-2 shadow-md transition-all">
                    <EditorCommandEmpty className="px-2 text-muted-foreground">No results</EditorCommandEmpty>
                    <EditorCommandList>
                        {suggestionItems.map((item) => (
                            <EditorCommandItem
                                value={item.title}
                                onCommand={(val) => item.command!(val)}
                                className="flex w-full cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-accent aria-selected:bg-accent"
                                key={item.title}
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-border bg-background">
                                    {item.icon}
                                </div>
                                <div>
                                    <p className="font-medium">{item.title}</p>
                                    <p className="text-xs text-muted-foreground">{item.description}</p>
                                </div>
                            </EditorCommandItem>
                        ))}
                    </EditorCommandList>
                </EditorCommand>

                <EditorBubble
                    tippyOptions={{
                        placement: 'top',
                    }}
                    className="flex w-fit max-w-[90vw] overflow-hidden rounded-lg border border-muted bg-background shadow-xl"
                >
                    <NodeSelector open={openNode} onOpenChange={setOpenNode} />
                    <LinkSelector open={openLink} onOpenChange={setOpenLink} />
                    <TextButtons />
                    <ColorSelector open={openColor} onOpenChange={setOpenColor} />
                </EditorBubble>
            </EditorContent>
        </EditorRoot>
    );
};
export default RichTextEditor;
