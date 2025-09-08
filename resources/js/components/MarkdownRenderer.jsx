import React from 'react';
import { Box, Text } from '@shopify/polaris';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import rehypeHighlight from 'rehype-highlight';
import rehypeSlug from 'rehype-slug';

const MarkdownRenderer = ({ content }) => {
    const markdownComponents = {
        h1: ({ children, ...props }) => (
            <Text variant="heading2xl" as="h1" {...props} style={{ marginBottom: '1.5rem', marginTop: '1rem', padding: '0.5rem 0', fontSize: '2.25rem', fontWeight: '700' }}>
                {children}
            </Text>
        ),
        h2: ({ children, ...props }) => (
            <Text variant="headingXl" as="h2" {...props} style={{ marginTop: '2.5rem', marginBottom: '1.25rem', padding: '0.5rem 0', fontSize: '1.875rem', fontWeight: '600' }}>
                {children}
            </Text>
        ),
        h3: ({ children, ...props }) => (
            <Text variant="headingLg" as="h3" {...props} style={{ marginTop: '2rem', marginBottom: '1rem', padding: '0.25rem 0', fontSize: '1.5rem', fontWeight: '600' }}>
                {children}
            </Text>
        ),
        h4: ({ children, ...props }) => (
            <Text variant="headingMd" as="h4" {...props} style={{ marginTop: '1.5rem', marginBottom: '0.75rem', padding: '0.25rem 0', fontSize: '1.25rem', fontWeight: '500' }}>
                {children}
            </Text>
        ),
        p: ({ children, ...props }) => (
            <Text variant="bodyMd" as="p" {...props} style={{ marginBottom: '1.25rem', lineHeight: '1.7', fontSize: '1rem', padding: '0.25rem 0' }}>
                {children}
            </Text>
        ),
        ul: ({ children, ...props }) => (
            <ul {...props} style={{ marginBottom: '1rem', paddingLeft: '1.5rem' }}>
                {children}
            </ul>
        ),
        ol: ({ children, ...props }) => (
            <ol {...props} style={{ marginBottom: '1rem', paddingLeft: '1.5rem' }}>
                {children}
            </ol>
        ),
        li: ({ children, ...props }) => (
            <li {...props} style={{ marginBottom: '0.5rem' }}>
                <Text variant="bodyMd" as="span">
                    {children}
                </Text>
            </li>
        ),
        blockquote: ({ children, ...props }) => (
            <Box 
                {...props} 
                padding="400" 
                background="bg-surface-secondary" 
                borderInlineStartWidth="4px" 
                borderInlineStartColor="border-info"
                style={{ marginBottom: '1rem' }}
            >
                <Text variant="bodyMd" as="div" tone="subdued" fontStyle="italic">
                    {children}
                </Text>
            </Box>
        ),
        code: ({ inline, children, ...props }) => {
            if (inline) {
                return (
                    <Text
                        variant="bodyMd"
                        as="code"
                        {...props}
                        style={{
                            backgroundColor: 'var(--p-color-bg-surface-secondary)',
                            padding: '0.2rem 0.4rem',
                            borderRadius: '3px',
                            fontFamily: 'monospace',
                            fontSize: '0.875em'
                        }}
                    >
                        {children}
                    </Text>
                );
            }
            return (
                <pre 
                    {...props} 
                    style={{ 
                        backgroundColor: 'var(--p-color-bg-surface-secondary)',
                        padding: '1rem',
                        borderRadius: '6px',
                        overflow: 'auto',
                        marginBottom: '1rem',
                        fontFamily: 'monospace'
                    }}
                >
                    <code>{children}</code>
                </pre>
            );
        },
        table: ({ children, ...props }) => (
            <Box style={{ overflow: 'auto', marginBottom: '1rem' }}>
                <table 
                    {...props} 
                    style={{ 
                        width: '100%', 
                        borderCollapse: 'collapse',
                        border: '1px solid var(--p-color-border)',
                    }}
                >
                    {children}
                </table>
            </Box>
        ),
        th: ({ children, ...props }) => (
            <th 
                {...props} 
                style={{ 
                    border: '1px solid var(--p-color-border)',
                    padding: '0.75rem',
                    backgroundColor: 'var(--p-color-bg-surface-secondary)',
                    textAlign: 'left'
                }}
            >
                <Text variant="bodyMd" as="span" fontWeight="semibold">
                    {children}
                </Text>
            </th>
        ),
        td: ({ children, ...props }) => (
            <td 
                {...props} 
                style={{ 
                    border: '1px solid var(--p-color-border)',
                    padding: '0.75rem',
                    textAlign: 'left'
                }}
            >
                <Text variant="bodyMd" as="span">
                    {children}
                </Text>
            </td>
        ),
        strong: ({ children, ...props }) => (
            <Text variant="bodyMd" as="strong" fontWeight="bold" {...props}>
                {children}
            </Text>
        ),
        em: ({ children, ...props }) => (
            <Text variant="bodyMd" as="em" fontStyle="italic" {...props}>
                {children}
            </Text>
        ),
        a: ({ children, href, ...props }) => {
            // Check if the link is external (starts with http:// or https://)
            const isExternal = href && (href.startsWith('http://') || href.startsWith('https://'));
            
            return (
                <a
                    href={href}
                    {...props}
                    target={isExternal ? '_blank' : '_self'}
                    rel={isExternal ? 'noopener noreferrer' : undefined}
                    style={{
                        color: 'var(--p-color-text-link)',
                        textDecoration: 'none',
                        cursor: 'pointer'
                    }}
                    onMouseEnter={(e) => e.target.style.textDecoration = 'underline'}
                    onMouseLeave={(e) => e.target.style.textDecoration = 'none'}
                >
                    {children}
                </a>
            );
        },
    };

    return (
        <Box id="doc-content" className="documentation-content">
            <ReactMarkdown
                components={markdownComponents}
                remarkPlugins={[remarkGfm]}
                rehypePlugins={[rehypeHighlight, rehypeSlug]}
            >
                {content}
            </ReactMarkdown>
        </Box>
    );
};

export default MarkdownRenderer;