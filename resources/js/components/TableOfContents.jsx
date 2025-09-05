import { useState, useEffect } from "react";
import {
    Box,
    Text,
    Button,
    BlockStack,
    Card,
    Collapsible
} from "@shopify/polaris";
import { ListIcon, ChevronDownIcon, ChevronUpIcon } from "@shopify/polaris-icons";

// =============================================================================
// ⚠️  TABLE OF CONTENTS - Auto-generated TOC from markdown headings
// =============================================================================
// This component automatically generates a table of contents from markdown
// headings and provides smooth scrolling navigation within the document
// =============================================================================

const TableOfContents = ({ content, className = "" }) => {
    const [headings, setHeadings] = useState([]);
    const [activeHeading, setActiveHeading] = useState("");
    const [isExpanded, setIsExpanded] = useState(true);
    
    // Extract headings from markdown content
    useEffect(() => {
        if (!content) return;
        
        const lines = content.split('\n');
        const extractedHeadings = [];
        
        lines.forEach((line, index) => {
            const trimmedLine = line.trim();
            if (trimmedLine.startsWith('#')) {
                const level = (trimmedLine.match(/^#+/) || [''])[0].length;
                const text = trimmedLine.replace(/^#+\s*/, '');
                const id = text.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-');
                
                // Only include headings level 1-4
                if (level <= 4) {
                    extractedHeadings.push({
                        id,
                        text,
                        level,
                        lineNumber: index
                    });
                }
            }
        });
        
        setHeadings(extractedHeadings);
        
        // Set first heading as active by default
        if (extractedHeadings.length > 0) {
            setActiveHeading(extractedHeadings[0].id);
        }
    }, [content]);
    
    // Handle intersection observer for active heading detection
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActiveHeading(entry.target.id);
                    }
                });
            },
            {
                rootMargin: '-100px 0px -80% 0px',
                threshold: 0.1
            }
        );
        
        // Observe all heading elements
        headings.forEach((heading) => {
            const element = document.getElementById(heading.id);
            if (element) {
                observer.observe(element);
            }
        });
        
        return () => observer.disconnect();
    }, [headings]);
    
    const scrollToHeading = (headingId) => {
        const element = document.getElementById(headingId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    };
    
    // Don't render if no headings
    if (headings.length === 0) {
        return null;
    }
    
    return (
        <Card className={className}>
            <Box padding="400">
                <BlockStack gap="300">
                    {/* TOC Header */}
                    <Button
                        onClick={() => setIsExpanded(!isExpanded)}
                        plain
                        fullWidth
                        textAlign="start"
                        icon={ListIcon}
                        accessibilityLabel="Toggle table of contents"
                    >
                        <Box>
                            <Text variant="headingSm" as="h4">
                                Table of Contents
                            </Text>
                            <Box paddingBlockStart="100">
                                <Text variant="caption" tone="subdued">
                                    {headings.length} {headings.length === 1 ? 'section' : 'sections'}
                                </Text>
                            </Box>
                        </Box>
                    </Button>
                    
                    {/* TOC Content */}
                    <Collapsible open={isExpanded}>
                        <BlockStack gap="100">
                            {headings.map((heading) => {
                                const isActive = activeHeading === heading.id;
                                const indentLevel = Math.min(heading.level - 1, 3); // Max 4 levels
                                
                                return (
                                    <Box
                                        key={heading.id}
                                        paddingInlineStart={`${indentLevel * 200}`}
                                    >
                                        <Button
                                            onClick={() => scrollToHeading(heading.id)}
                                            plain={!isActive}
                                            primary={isActive}
                                            fullWidth
                                            textAlign="start"
                                            size="slim"
                                        >
                                            <Text 
                                                variant={heading.level <= 2 ? "bodySm" : "caption"}
                                                fontWeight={isActive ? "semibold" : "regular"}
                                                tone={isActive ? "base" : "subdued"}
                                            >
                                                {heading.text}
                                            </Text>
                                        </Button>
                                    </Box>
                                );
                            })}
                        </BlockStack>
                    </Collapsible>
                    
                    {/* Expand/Collapse Toggle */}
                    <Box borderBlockStartWidth="1" borderColor="border-subdued" paddingBlockStart="300">
                        <Button
                            onClick={() => setIsExpanded(!isExpanded)}
                            plain
                            size="slim"
                            icon={isExpanded ? ChevronUpIcon : ChevronDownIcon}
                        >
                            {isExpanded ? 'Collapse' : 'Expand'}
                        </Button>
                    </Box>
                </BlockStack>
            </Box>
        </Card>
    );
};

export default TableOfContents;