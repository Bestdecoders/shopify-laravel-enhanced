import { useState } from "react";
import {
    Box,
    Text,
    Button,
    BlockStack,
    Collapsible,
    InlineStack,
    Badge
} from "@shopify/polaris";
import { ChevronDownIcon, ChevronRightIcon } from "@shopify/polaris-icons";

// =============================================================================
// ⚠️  DOCUMENTATION NAVIGATION - Sidebar navigation for docs
// =============================================================================
// This component provides hierarchical navigation for documentation sections
// and pages with collapsible sections and active page highlighting
// =============================================================================

const DocNavigation = ({ docsConfig, currentPage, onPageSelect }) => {
    const [expandedSections, setExpandedSections] = useState({});
    
    const { documentation = {} } = docsConfig;
    const { sections = [] } = documentation;
    
    const toggleSection = (sectionId) => {
        setExpandedSections(prev => ({
            ...prev,
            [sectionId]: !prev[sectionId]
        }));
    };
    
    const isPageActive = (pageId) => {
        return currentPage === pageId;
    };
    
    const isSectionActive = (section) => {
        return section.pages?.some(page => isPageActive(page.id));
    };
    
    const getSectionExpansion = (sectionId) => {
        // Auto-expand section if it contains the active page
        const section = sections.find(s => s.id === sectionId);
        if (section && isSectionActive(section)) {
            return true;
        }
        return expandedSections[sectionId] || false;
    };
    
    return (
        <Box>
            <BlockStack gap="300">
                {/* Documentation Header */}
                <Box paddingBlockEnd="400">
                    <Text variant="headingSm" as="h3">
                        {documentation.title || "Documentation"}
                    </Text>
                    {documentation.description && (
                        <Box paddingBlockStart="200">
                            <Text variant="caption" tone="subdued">
                                {documentation.description.replace('{appName}', docsConfig.appConfig?.appName || 'Your App')}
                            </Text>
                        </Box>
                    )}
                </Box>
                
                {/* Navigation Sections */}
                {sections.map((section) => {
                    const isExpanded = getSectionExpansion(section.id);
                    const hasActivePages = isSectionActive(section);
                    
                    return (
                        <Box key={section.id}>
                            {/* Section Header */}
                            <Button
                                onClick={() => toggleSection(section.id)}
                                plain
                                fullWidth
                                textAlign="start"
                                icon={isExpanded ? ChevronDownIcon : ChevronRightIcon}
                            >
                                <InlineStack gap="200" align="space-between" blockAlign="center">
                                    <InlineStack gap="200" align="start">
                                        {section.icon && <Text>{section.icon}</Text>}
                                        <Text 
                                            variant="bodySm" 
                                            fontWeight={hasActivePages ? "semibold" : "regular"}
                                            tone={hasActivePages ? "base" : "subdued"}
                                        >
                                            {section.title}
                                        </Text>
                                    </InlineStack>
                                    {section.pages && (
                                        <Badge tone="subdued" size="small">
                                            {section.pages.length}
                                        </Badge>
                                    )}
                                </InlineStack>
                            </Button>
                            
                            {/* Section Pages */}
                            <Collapsible open={isExpanded}>
                                <Box paddingInlineStart="400" paddingBlockStart="200">
                                    <BlockStack gap="100">
                                        {section.pages?.map((page) => {
                                            const isActive = isPageActive(page.id);
                                            
                                            return (
                                                <Button
                                                    key={page.id}
                                                    onClick={() => onPageSelect(page.id)}
                                                    plain={!isActive}
                                                    primary={isActive}
                                                    fullWidth
                                                    textAlign="start"
                                                    size="slim"
                                                >
                                                    <Box>
                                                        <Text 
                                                            variant="bodySm"
                                                            fontWeight={isActive ? "medium" : "regular"}
                                                        >
                                                            {page.title}
                                                        </Text>
                                                        {page.description && (
                                                            <Box paddingBlockStart="050">
                                                                <Text variant="caption" tone="subdued">
                                                                    {page.description}
                                                                </Text>
                                                            </Box>
                                                        )}
                                                    </Box>
                                                </Button>
                                            );
                                        })}
                                    </BlockStack>
                                </Box>
                            </Collapsible>
                        </Box>
                    );
                })}
                
                {/* No sections fallback */}
                {sections.length === 0 && (
                    <Box padding="400" textAlign="center">
                        <Text variant="bodySm" tone="subdued">
                            No documentation sections available
                        </Text>
                    </Box>
                )}
            </BlockStack>
        </Box>
    );
};

export default DocNavigation;