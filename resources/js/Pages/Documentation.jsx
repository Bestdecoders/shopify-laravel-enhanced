import React, { useState, useEffect } from 'react';
import { Page, Card, Box, Text, Button, InlineStack, BlockStack, Divider, Spinner, Badge } from '@shopify/polaris';
import { BookIcon, ArrowUpIcon, ArrowLeftIcon } from '@shopify/polaris-icons';
import { router } from '@inertiajs/react';
import { useAxios } from '../hooks/useAxios';
import SearchBar from '../components/SearchBar';
import MarkdownRenderer from '../components/MarkdownRenderer';
import TableOfContents from '../components/TableOfContents';
import 'highlight.js/styles/github.css';

const Documentation = ({ docs = [], categories = [] }) => {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [showSearchResults, setShowSearchResults] = useState(false);
    const [currentDoc, setCurrentDoc] = useState(docs[0] || null); // Load first doc by default
    const [content, setContent] = useState('');
    const [toc, setToc] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeSection, setActiveSection] = useState('');
    const [showScrollTop, setShowScrollTop] = useState(false);
    const axios = useAxios();

    // Load first document on mount
    useEffect(() => {
        if (docs[0]) {
            loadDocument(docs[0].id);
        }
    }, []);

    useEffect(() => {
        // Scroll listener for scroll-to-top button
        const handleScroll = () => {
            setShowScrollTop(window.pageYOffset > 300);
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    useEffect(() => {
        if (content) {
            // Intersection Observer for active section tracking
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            setActiveSection(entry.target.id);
                        }
                    });
                },
                { 
                    rootMargin: '-20% 0px -80% 0px',
                    threshold: 0 
                }
            );

            // Small delay to ensure DOM is ready
            const timer = setTimeout(() => {
                const headers = document.querySelectorAll('#doc-content h1, #doc-content h2, #doc-content h3, #doc-content h4, #doc-content h5, #doc-content h6');
                headers.forEach(header => observer.observe(header));
            }, 100);

            return () => {
                clearTimeout(timer);
                observer.disconnect();
            };
        }
    }, [content]);

    const loadDocument = async (docId) => {
        setLoading(true);
        try {
            const response = await axios.get(`/docs/api/${docId}`);
            setCurrentDoc(response.data.doc);
            setContent(response.data.content);
            setToc(response.data.toc);
        } catch (error) {
            console.error('Failed to load document:', error);
        } finally {
            setLoading(false);
        }
    };

    // Real-time search on typing
    useEffect(() => {
        const performSearch = async () => {
            if (searchQuery.trim()) {
                try {
                    const response = await axios.get('/docs/search', {
                        params: { q: searchQuery.trim() }
                    });
                    setSearchResults(response.data.results || []);
                    setShowSearchResults(true);
                } catch (error) {
                    console.error('Search failed:', error);
                    setSearchResults([]);
                    setShowSearchResults(false);
                }
            } else {
                setSearchResults([]);
                setShowSearchResults(false);
            }
        };

        const timer = setTimeout(performSearch, 300);
        return () => clearTimeout(timer);
    }, [searchQuery]);

    const handleSearch = async () => {
        if (searchQuery.trim()) {
            try {
                const response = await axios.get('/docs/search', {
                    params: { q: searchQuery.trim() }
                });
                setSearchResults(response.data.results || []);
                setShowSearchResults(true);
            } catch (error) {
                console.error('Search failed:', error);
            }
        }
    };

    const openSearchResult = (docId) => {
        loadDocument(docId);
        setShowSearchResults(false);
        setSearchQuery('');
    };

    const scrollToSection = (slug) => {
        const element = document.getElementById(slug);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const backToDashboard = () => {
        router.visit('/home'); // Adjust the path as needed
    }

    // Group docs by category
    const docsByCategory = categories.map(category => ({
        category,
        docs: docs.filter(doc => doc.category === category)
    }));

    return (
        <Page 
            title="Documentation" 
            backAction={{ content: "Back", onAction: backToDashboard }}
            fullWidth
        >
            <div style={{ display: 'flex', height: '100vh', gap: '1rem' }}>
                {/* Left Sidebar - Documentation Navigation */}
                <div style={{ 
                    width: '320px', 
                    flexShrink: 0,
                    height: '100%',
                    overflowY: 'auto'
                }}>
                    <Card>
                        <Box padding="400">
                            <BlockStack gap="400">
                                {/* Back to Dashboard Button */}

                                <Divider />

                                {/* Sidebar Header */}
                                <Box>
                                    <Text variant="headingMd" as="h2">
                                        📚 Documentation
                                    </Text>
                                    <Text variant="bodySm" tone="subdued">
                                        {docs.length} articles • {categories.length} categories
                                    </Text>
                                </Box>

                                <Divider />

                                {/* Documentation by Category */}
                                {docsByCategory.map(({ category, docs: categoryDocs }) => (
                                    <Box key={category}>
                                        <Text variant="bodyMd" as="h3" fontWeight="semibold" tone="subdued">
                                            {category}
                                        </Text>
                                        <Box paddingBlockStart="200">
                                            <BlockStack gap="100">
                                                {categoryDocs.map((doc) => (
                                                    <Button
                                                        key={doc.id}
                                                        variant={currentDoc?.id === doc.id ? "primary" : "plain"}
                                                        textAlign="start"
                                                        size="slim"
                                                        onClick={() => loadDocument(doc.id)}
                                                        style={{ 
                                                            justifyContent: 'flex-start',
                                                            width: '100%'
                                                        }}
                                                    >
                                                        <InlineStack gap="200" align="start" blockAlign="center">
                                                            <BookIcon style={{ 
                                                                width: '14px', 
                                                                height: '14px',
                                                                flexShrink: 0
                                                            }} />
                                                            <Text variant="bodySm" truncate>
                                                                {doc.title}
                                                            </Text>
                                                        </InlineStack>
                                                    </Button>
                                                ))}
                                            </BlockStack>
                                        </Box>
                                    </Box>
                                ))}

                                <Divider />

                                {/* Quick Stats */}
                                <Box padding="300" background="bg-surface-secondary" borderRadius="200">
                                    <BlockStack gap="200">
                                        <Text variant="headingSm" as="h3">
                                            📊 Quick Stats
                                        </Text>
                                        <InlineStack gap="300">
                                            <Badge tone="info">{docs.length} Docs</Badge>
                                            <Badge tone="success">{categories.length} Categories</Badge>
                                        </InlineStack>
                                    </BlockStack>
                                </Box>
                            </BlockStack>
                        </Box>
                    </Card>
                </div>

                {/* Main Content Area */}
                <div style={{ flex: 1, height: '100%', overflowY: 'auto', position: 'relative' }}>
                    <Card>
                        <Box padding="500">
                            <BlockStack gap="500">
                                {/* Search Bar Component */}
                                <SearchBar
                                    searchQuery={searchQuery}
                                    onSearchChange={setSearchQuery}
                                    onSearch={handleSearch}
                                    searchResults={searchResults}
                                    showSearchResults={showSearchResults}
                                    onResultClick={openSearchResult}
                                />

                                {loading ? (
                                    <div style={{ 
                                        display: 'flex', 
                                        justifyContent: 'center', 
                                        alignItems: 'center', 
                                        height: '400px' 
                                    }}>
                                        <Spinner accessibilityLabel="Loading documentation" size="large" />
                                    </div>
                                ) : currentDoc ? (
                                    <>
                                        {/* Document Header */}
                                        <Box>
                                            <Text variant="bodySm" tone="subdued" as="p">
                                                {currentDoc.category}
                                            </Text>
                                            <Text variant="heading2xl" as="h1" style={{ margin: '0.5rem 0' }}>
                                                {currentDoc.title}
                                            </Text>
                                            <Text variant="bodyLg" tone="subdued" as="p">
                                                {currentDoc.description}
                                            </Text>
                                        </Box>

                                        <Divider />

                                        {/* Document Content */}
                                        <MarkdownRenderer content={content} />
                                    </>
                                ) : (
                                    <Box padding="600" style={{ textAlign: 'center' }}>
                                        <Text variant="headingMd" as="h2">
                                            Welcome to Documentation
                                        </Text>
                                        <Text variant="bodyMd" tone="subdued">
                                            Select an article from the sidebar to get started.
                                        </Text>
                                    </Box>
                                )}
                            </BlockStack>
                        </Box>
                    </Card>

                    {/* Table of Contents Component */}
                    <TableOfContents
                        toc={toc}
                        activeSection={activeSection}
                        onSectionClick={scrollToSection}
                    />
                </div>
            </div>

            {/* Floating Scroll to Top Button */}
            {showScrollTop && (
                <div style={{ 
                    position: 'fixed', 
                    bottom: '2rem', 
                    left: '50%',
                    transform: 'translateX(-50%)',
                    zIndex: 100 
                }}>
                    <Button 
                        onClick={scrollToTop}
                        icon={ArrowUpIcon}
                        variant="primary"
                        size="large"
                        accessibilityLabel="Scroll to top"
                    />
                </div>
            )}
        </Page>
    );
};

export default Documentation;