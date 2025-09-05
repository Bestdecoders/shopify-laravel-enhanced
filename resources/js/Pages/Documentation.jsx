import { useState, useEffect } from "react";
import {
    Page,
    InlineGrid,
    BlockStack,
    Card,
    Box,
    Text,
    Spinner,
    EmptyState,
    TextField,
    Button,
    Badge,
    InlineStack
} from "@shopify/polaris";
import { SearchIcon, BookIcon, ExternalIcon, ArrowLeftIcon } from "@shopify/polaris-icons";
import DocNavigation from "../components/DocNavigation";
import MarkdownRenderer from "../components/MarkdownRenderer";
import TableOfContents from "../components/TableOfContents";

// =============================================================================
// ⚠️  DOCUMENTATION SYSTEM - Complete documentation with TOC and search
// =============================================================================
// This component provides a complete documentation system with:
// - JSON-based configuration
// - Markdown content rendering with Polaris styling
// - Table of contents generation
// - Search functionality
// - Navigation between pages
// =============================================================================

export default function Documentation() {
    // State management
    const [docsConfig, setDocsConfig] = useState({});
    const [currentPage, setCurrentPage] = useState("introduction");
    const [currentContent, setCurrentContent] = useState("");
    const [loading, setLoading] = useState(true);
    const [contentLoading, setContentLoading] = useState(false);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState("");
    const [searchResults, setSearchResults] = useState([]);
    
    // Load documentation configuration
    useEffect(() => {
        const loadDocsConfig = async () => {
            try {
                setLoading(true);
                const response = await fetch('/resources/data/docs-config.json');
                if (!response.ok) {
                    throw new Error('Failed to load documentation config');
                }
                const config = await response.json();
                setDocsConfig(config);
                
                // Load the first page by default
                if (config.documentation?.sections?.[0]?.pages?.[0]) {
                    const firstPage = config.documentation.sections[0].pages[0];
                    setCurrentPage(firstPage.id);
                    await loadPageContent(firstPage.file);
                }
            } catch (err) {
                console.error('Error loading docs config:', err);
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadDocsConfig();
    }, []);
    
    // Load markdown content for a page
    const loadPageContent = async (fileName) => {
        try {
            setContentLoading(true);
            const response = await fetch(`/resources/docs/${fileName}`);
            if (!response.ok) {
                throw new Error(`Failed to load ${fileName}`);
            }
            const content = await response.text();
            setCurrentContent(content);
        } catch (err) {
            console.error('Error loading page content:', err);
            setCurrentContent(`# Error Loading Content\n\nCould not load ${fileName}. Please check that the file exists.`);
        } finally {
            setContentLoading(false);
        }
    };
    
    // Handle page selection from navigation
    const handlePageSelect = (pageId) => {
        // Find the page in the config
        let selectedPage = null;
        for (const section of docsConfig.documentation?.sections || []) {
            selectedPage = section.pages?.find(page => page.id === pageId);
            if (selectedPage) break;
        }
        
        if (selectedPage) {
            setCurrentPage(pageId);
            loadPageContent(selectedPage.file);
        }
    };
    
    // Search functionality
    const performSearch = (term) => {
        if (!term.trim()) {
            setSearchResults([]);
            return;
        }
        
        const results = [];
        const searchLower = term.toLowerCase();
        
        for (const section of docsConfig.documentation?.sections || []) {
            for (const page of section.pages || []) {
                // Search in page title and description
                const titleMatch = page.title.toLowerCase().includes(searchLower);
                const descMatch = page.description?.toLowerCase().includes(searchLower);
                
                if (titleMatch || descMatch) {
                    results.push({
                        ...page,
                        section: section.title,
                        sectionIcon: section.icon
                    });
                }
            }
        }
        
        setSearchResults(results);
    };
    
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            performSearch(searchTerm);
        }, 300);
        
        return () => clearTimeout(timeoutId);
    }, [searchTerm, docsConfig]);
    
    // Get current page info
    const getCurrentPageInfo = () => {
        for (const section of docsConfig.documentation?.sections || []) {
            const page = section.pages?.find(p => p.id === currentPage);
            if (page) {
                return { page, section };
            }
        }
        return { page: null, section: null };
    };
    
    const { page: currentPageInfo, section: currentSection } = getCurrentPageInfo();
    
    // Loading state
    if (loading) {
        return (
            <Page title="Documentation" fullWidth>
                <BlockStack gap="400">
                    <Card sectioned>
                        <Box padding="400">
                            <Button
                                icon={ArrowLeftIcon}
                                onClick={() => window.location.href = '/home'}
                                primary
                            >
                                Back to Dashboard
                            </Button>
                        </Box>
                    </Card>
                    <Card sectioned>
                        <Box padding="600" textAlign="center">
                            <Spinner size="large" />
                            <Box paddingBlockStart="300">
                                <Text variant="bodyMd" tone="subdued">
                                    Loading documentation...
                                </Text>
                            </Box>
                        </Box>
                    </Card>
                </BlockStack>
            </Page>
        );
    }
    
    // Error state
    if (error) {
        return (
            <Page title="Documentation" fullWidth>
                <BlockStack gap="400">
                    <Card sectioned>
                        <Box padding="400">
                            <Button
                                icon={ArrowLeftIcon}
                                onClick={() => window.location.href = '/home'}
                                primary
                            >
                                Back to Dashboard
                            </Button>
                        </Box>
                    </Card>
                    <Card sectioned>
                        <EmptyState
                            heading="Failed to load documentation"
                            image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                        >
                            <p>We couldn't load the documentation. Please try refreshing the page.</p>
                            <p>Error: {error}</p>
                        </EmptyState>
                    </Card>
                </BlockStack>
            </Page>
        );
    }
    
    return (
        <Page title="Documentation" fullWidth>
            <InlineGrid columns={{ xs: 1, lg: '2.5fr 1fr' }} gap="400">
                {/* Main Content Area */}
                <BlockStack gap="400">
                    {/* Back to Dashboard Button */}
                    <Card sectioned>
                        <Box padding="400">
                            <Button
                                icon={ArrowLeftIcon}
                                onClick={() => window.location.href = '/home'}
                                primary
                            >
                                Back to Dashboard
                            </Button>
                        </Box>
                    </Card>
                    {/* Search Bar */}
                    <Card sectioned>
                        <Box padding="400">
                            <BlockStack gap="300">
                                <TextField
                                    label="Search documentation"
                                    value={searchTerm}
                                    onChange={setSearchTerm}
                                    placeholder="Search pages, content, and features..."
                                    prefix={<SearchIcon />}
                                    clearButton
                                    onClearButtonClick={() => setSearchTerm("")}
                                />
                                
                                {/* Search Results */}
                                {searchResults.length > 0 && (
                                    <Card>
                                        <Box padding="400">
                                            <BlockStack gap="200">
                                                <Text variant="headingSm">
                                                    Search Results ({searchResults.length})
                                                </Text>
                                                {searchResults.slice(0, 5).map((result) => (
                                                    <Button
                                                        key={result.id}
                                                        onClick={() => handlePageSelect(result.id)}
                                                        plain
                                                        fullWidth
                                                        textAlign="start"
                                                    >
                                                        <InlineStack gap="200" align="start">
                                                            <Text>{result.sectionIcon}</Text>
                                                            <Box>
                                                                <Text variant="bodySm" fontWeight="medium">
                                                                    {result.title}
                                                                </Text>
                                                                <Text variant="caption" tone="subdued">
                                                                    {result.section} • {result.description}
                                                                </Text>
                                                            </Box>
                                                        </InlineStack>
                                                    </Button>
                                                ))}
                                                {searchResults.length > 5 && (
                                                    <Text variant="caption" tone="subdued">
                                                        And {searchResults.length - 5} more results...
                                                    </Text>
                                                )}
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                )}
                            </BlockStack>
                        </Box>
                    </Card>
                    
                    {/* Breadcrumb */}
                    {currentSection && currentPageInfo && (
                        <Card sectioned>
                            <Box padding="200">
                                <InlineStack gap="200" align="start">
                                    <Badge tone="info">{currentSection.icon} {currentSection.title}</Badge>
                                    <Text variant="bodySm" tone="subdued">•</Text>
                                    <Text variant="bodySm">{currentPageInfo.title}</Text>
                                </InlineStack>
                            </Box>
                        </Card>
                    )}
                    
                    {/* Main Content */}
                    <Card sectioned>
                        <Box padding="600">
                            {contentLoading ? (
                                <Box textAlign="center" padding="600">
                                    <Spinner size="small" />
                                    <Box paddingBlockStart="200">
                                        <Text variant="bodySm" tone="subdued">Loading content...</Text>
                                    </Box>
                                </Box>
                            ) : (
                                <MarkdownRenderer content={currentContent} />
                            )}
                        </Box>
                    </Card>
                    
                    {/* Page Navigation */}
                    {currentPageInfo && (
                        <Card sectioned>
                            <Box padding="400">
                                <InlineStack gap="300" align="space-between">
                                    <Button
                                        icon={ExternalIcon}
                                        onClick={() => window.open('/support', '_blank')}
                                    >
                                        Need Help?
                                    </Button>
                                    <InlineStack gap="200">
                                        <Text variant="caption" tone="subdued">
                                            Last updated: {currentPageInfo.lastUpdated || 'Recently'}
                                        </Text>
                                        <Button
                                            plain
                                            icon={ExternalIcon}
                                            onClick={() => window.print()}
                                        >
                                            Print
                                        </Button>
                                    </InlineStack>
                                </InlineStack>
                            </Box>
                        </Card>
                    )}
                </BlockStack>
                
                {/* Right Sidebar - TOC and Doc Navigation */}
                <BlockStack gap="400">
                    {/* Table of Contents */}
                    <TableOfContents content={currentContent} />
                    
                    {/* Documentation Navigation */}
                    <Card sectioned>
                        <Box padding="400">
                            <DocNavigation 
                                docsConfig={docsConfig}
                                currentPage={currentPage}
                                onPageSelect={handlePageSelect}
                            />
                        </Box>
                    </Card>
                </BlockStack>
            </InlineGrid>
        </Page>
    );
}