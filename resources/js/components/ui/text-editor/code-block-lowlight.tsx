import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { NodeViewContent, NodeViewWrapper, ReactNodeViewRenderer } from '@tiptap/react';
import { cx } from 'class-variance-authority';
import { common, createLowlight } from 'lowlight';

// Create lowlight instance with common languages
const lowlight = createLowlight(common);

// Custom component for code block
function CodeBlock() {
    return (
        <NodeViewWrapper className="code-block-wrapper">
            <pre
                className={cx(
                    'rounded-md border border-border bg-muted p-5',
                    'font-mono text-sm font-medium text-foreground',
                    'hljs overflow-x-auto',
                )}
            >
                <NodeViewContent as="code" />
            </pre>
        </NodeViewWrapper>
    );
}

// Export the code block extension with lowlight syntax highlighting
export const CodeBlockExtension = CodeBlockLowlight.extend({
    addNodeView() {
        return ReactNodeViewRenderer(CodeBlock);
    },
}).configure({
    lowlight,
    defaultLanguage: null, // Auto-detect language
});
