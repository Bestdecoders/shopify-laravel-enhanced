import {
    Text,
    Card,
    Box,
    BlockStack,
    InlineStack,
    Divider,
    List,
    Link,
    Badge,
    DataTable
} from "@shopify/polaris";

// =============================================================================
// ⚠️  MARKDOWN RENDERER - Renders Markdown with Polaris Components
// =============================================================================
// This component processes markdown content and renders it using Shopify Polaris
// components for consistent styling with the rest of the application
// =============================================================================

const MarkdownRenderer = ({ content, className = "" }) => {
    // Simple markdown parser - in a real implementation, you'd use react-markdown
    // For now, this provides the structure and styling framework
    
    const parseMarkdown = (markdown) => {
        if (!markdown) return [];
        
        const lines = markdown.split('\n');
        const elements = [];
        let currentElement = null;
        let inCodeBlock = false;
        let inTable = false;
        let tableHeaders = [];
        let tableRows = [];
        
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const trimmedLine = line.trim();
            
            // Skip frontmatter
            if (trimmedLine === '---') continue;
            if (trimmedLine.startsWith('title:') || 
                trimmedLine.startsWith('description:') || 
                trimmedLine.startsWith('lastUpdated:')) continue;
            
            // Handle code blocks
            if (trimmedLine.startsWith('```')) {
                inCodeBlock = !inCodeBlock;
                if (!inCodeBlock && currentElement?.type === 'code') {
                    elements.push(currentElement);
                    currentElement = null;
                } else if (inCodeBlock) {
                    currentElement = { type: 'code', content: [] };
                }
                continue;
            }
            
            if (inCodeBlock) {
                currentElement.content.push(line);
                continue;
            }
            
            // Handle tables
            if (trimmedLine.includes('|') && trimmedLine.split('|').length > 2) {
                if (!inTable) {
                    inTable = true;
                    tableHeaders = trimmedLine.split('|').map(h => h.trim()).filter(h => h);
                    continue;
                }
                
                // Skip separator line
                if (trimmedLine.includes('---')) continue;
                
                const rowData = trimmedLine.split('|').map(cell => cell.trim()).filter(cell => cell);
                if (rowData.length > 0) {
                    tableRows.push(rowData);
                }
                continue;
            } else if (inTable) {
                // End of table
                elements.push({
                    type: 'table',
                    headers: tableHeaders,
                    rows: tableRows
                });
                inTable = false;
                tableHeaders = [];
                tableRows = [];
            }
            
            // Handle headings
            if (trimmedLine.startsWith('#')) {
                const level = (trimmedLine.match(/^#+/) || [''])[0].length;
                const text = trimmedLine.replace(/^#+\s*/, '');
                elements.push({ type: 'heading', level, text, id: text.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-') });
                continue;
            }
            
            // Handle lists
            if (trimmedLine.match(/^[-*+]\s/) || trimmedLine.match(/^\d+\.\s/)) {
                const isOrdered = trimmedLine.match(/^\d+\.\s/);
                const text = trimmedLine.replace(/^[-*+\d.]\s*/, '');
                
                if (currentElement?.type !== 'list' || currentElement?.ordered !== isOrdered) {
                    if (currentElement) elements.push(currentElement);
                    currentElement = { type: 'list', ordered: isOrdered, items: [] };
                }
                currentElement.items.push(text);
                continue;
            } else if (currentElement?.type === 'list') {
                elements.push(currentElement);
                currentElement = null;
            }
            
            // Handle blockquotes
            if (trimmedLine.startsWith('>')) {
                const text = trimmedLine.replace(/^>\s*/, '');
                elements.push({ type: 'blockquote', text });
                continue;
            }
            
            // Handle horizontal rules
            if (trimmedLine.match(/^---+$|^\*\*\*+$|^___+$/)) {
                elements.push({ type: 'divider' });
                continue;
            }
            
            // Handle empty lines
            if (trimmedLine === '') {
                if (currentElement) {
                    elements.push(currentElement);
                    currentElement = null;
                }
                continue;
            }
            
            // Handle paragraphs
            if (currentElement?.type === 'paragraph') {
                currentElement.text += ' ' + trimmedLine;
            } else {
                if (currentElement) elements.push(currentElement);
                currentElement = { type: 'paragraph', text: trimmedLine };
            }
        }
        
        // Push final element
        if (currentElement) elements.push(currentElement);
        
        // Handle final table if exists
        if (inTable) {
            elements.push({
                type: 'table',
                headers: tableHeaders,
                rows: tableRows
            });
        }
        
        return elements;
    };
    
    const renderInlineText = (text) => {
        if (!text) return text;
        
        // Handle bold text
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Handle italic text
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Handle inline code
        text = text.replace(/`(.*?)`/g, '<code style="background: #f6f6f7; padding: 2px 4px; border-radius: 3px; font-family: monospace;">$1</code>');
        
        // Handle links
        text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
        
        return <span dangerouslySetInnerHTML={{ __html: text }} />;
    };
    
    const renderElement = (element, index) => {
        switch (element.type) {
            case 'heading':
                const HeadingComponent = element.level === 1 ? 'h1' : element.level === 2 ? 'h2' : element.level === 3 ? 'h3' : 'h4';
                const variant = element.level === 1 ? 'headingXl' : 
                              element.level === 2 ? 'headingLg' : 
                              element.level === 3 ? 'headingMd' : 'headingSm';
                
                return (
                    <Box key={index} paddingBlockStart={index > 0 ? "600" : "0"} paddingBlockEnd="400">
                        <Text variant={variant} as={HeadingComponent} id={element.id}>
                            {renderInlineText(element.text)}
                        </Text>
                    </Box>
                );
                
            case 'paragraph':
                return (
                    <Box key={index} paddingBlockEnd="400">
                        <Text variant="bodyMd">
                            {renderInlineText(element.text)}
                        </Text>
                    </Box>
                );
                
            case 'list':
                return (
                    <Box key={index} paddingBlockEnd="400">
                        <List type={element.ordered ? "number" : "bullet"}>
                            {element.items.map((item, itemIndex) => (
                                <List.Item key={itemIndex}>
                                    {renderInlineText(item)}
                                </List.Item>
                            ))}
                        </List>
                    </Box>
                );
                
            case 'blockquote':
                return (
                    <Box key={index} paddingBlockEnd="400">
                        <Card>
                            <Box padding="400" background="bg-surface-secondary" borderInlineStartWidth="4" borderColor="border-info">
                                <Text variant="bodyMd" tone="subdued">
                                    {renderInlineText(element.text)}
                                </Text>
                            </Box>
                        </Card>
                    </Box>
                );
                
            case 'code':
                return (
                    <Box key={index} paddingBlockEnd="400">
                        <Card>
                            <Box padding="400" background="bg-surface-tertiary">
                                <Text variant="bodySm" fontFamily="monospace">
                                    <pre style={{ margin: 0, whiteSpace: 'pre-wrap' }}>
                                        {element.content.join('\n')}
                                    </pre>
                                </Text>
                            </Box>
                        </Card>
                    </Box>
                );
                
            case 'table':
                const rows = element.rows.map(row => 
                    element.headers.map((header, colIndex) => row[colIndex] || '')
                );
                
                return (
                    <Box key={index} paddingBlockEnd="400">
                        <Card>
                            <DataTable
                                columnContentTypes={element.headers.map(() => 'text')}
                                headings={element.headers}
                                rows={rows}
                            />
                        </Card>
                    </Box>
                );
                
            case 'divider':
                return (
                    <Box key={index} paddingBlockStart="400" paddingBlockEnd="400">
                        <Divider />
                    </Box>
                );
                
            default:
                return null;
        }
    };
    
    const elements = parseMarkdown(content);
    
    return (
        <Box className={className}>
            <BlockStack gap="0">
                {elements.map((element, index) => renderElement(element, index))}
            </BlockStack>
        </Box>
    );
};

export default MarkdownRenderer;