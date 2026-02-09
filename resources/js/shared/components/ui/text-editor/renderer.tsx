import { EditorContent, EditorRoot, JSONContent } from 'novel';
import { useMemo } from 'react';
import { defaultExtensions } from './extensions';

interface RichTextRendererProps {
    content: JSONContent | null | undefined;
    className?: string;
}

export function RichTextRenderer({ content, className }: RichTextRendererProps) {
    const extensions = useMemo(() => defaultExtensions, []);

    if (!content) {
        return null;
    }

    return (
        <EditorRoot>
            <EditorContent
                initialContent={content}
                extensions={extensions}
                editable={false}
                className={className || 'prose prose-sm dark:prose-invert max-w-none'}
            />
        </EditorRoot>
    );
}
