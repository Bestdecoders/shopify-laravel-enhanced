import { useState, useEffect } from "react";
import {
    Box,
    Text,
    Collapsible,
    Card,
    BlockStack,
    InlineStack,
    Badge,
    Button,
    Icon,
    TextField,
    Spinner,
    EmptyState
} from "@shopify/polaris";
import { ChevronDownIcon, ChevronUpIcon, SearchIcon, FilterIcon } from "@shopify/polaris-icons";
import AppLayout from "../components/AppLayout";

// =============================================================================
// ⚠️  FAQ DATA LOADING - Data is loaded from JSON files
// =============================================================================
// FAQ data is loaded from resources/data/faq-data.json
// FAQ configuration is loaded from resources/data/faq-config.json
// To customize for your app, edit the JSON files instead of this component
// =============================================================================

export default function FAQSection() {
    // State management
    const [faqData, setFaqData] = useState([]);
    const [faqConfig, setFaqConfig] = useState({ categories: {}, appConfig: {} });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    // UI State
    const [openIndex, setOpenIndex] = useState(null);
    const [selectedCategory, setSelectedCategory] = useState("all");
    const [searchTerm, setSearchTerm] = useState("");
    const [selectedTags, setSelectedTags] = useState([]);
    const [showFilters, setShowFilters] = useState(false);

    // Load FAQ data and config on component mount
    useEffect(() => {
        const loadFAQData = async () => {
            try {
                setLoading(true);
                
                // Load FAQ data from JSON file
                const faqDataResponse = await fetch('/resources/data/faq-data.json');
                if (!faqDataResponse.ok) {
                    throw new Error('Failed to load FAQ data');
                }
                const faqDataJson = await faqDataResponse.json();
                
                // Load FAQ config from JSON file
                const faqConfigResponse = await fetch('/resources/data/faq-config.json');
                if (!faqConfigResponse.ok) {
                    throw new Error('Failed to load FAQ config');
                }
                const faqConfigJson = await faqConfigResponse.json();
                
                setFaqData(faqDataJson);
                setFaqConfig(faqConfigJson);
            } catch (err) {
                console.error('Error loading FAQ data:', err);
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadFAQData();
    }, []);

    // Get all unique tags from FAQ data
    const allTags = [...new Set(faqData.flatMap(faq => faq.tags || []))].sort();

    // Group FAQs by category
    const groupedFAQs = faqData.reduce((acc, faq, index) => {
        if (!acc[faq.category]) {
            acc[faq.category] = [];
        }
        acc[faq.category].push({ ...faq, originalIndex: index });
        return acc;
    }, {});

    // Advanced filtering function
    const getFilteredFAQs = () => {
        let filtered = faqData.map((faq, index) => ({ ...faq, originalIndex: index }));

        // Filter by category
        if (selectedCategory !== "all") {
            filtered = filtered.filter(faq => faq.category === selectedCategory);
        }

        // Filter by search term
        if (searchTerm.trim()) {
            const searchLower = searchTerm.toLowerCase();
            filtered = filtered.filter(faq => 
                faq.question.toLowerCase().includes(searchLower) ||
                faq.answer.toLowerCase().includes(searchLower) ||
                (faq.tags && faq.tags.some(tag => tag.toLowerCase().includes(searchLower)))
            );
        }

        // Filter by selected tags
        if (selectedTags.length > 0) {
            filtered = filtered.filter(faq => 
                faq.tags && selectedTags.some(tag => faq.tags.includes(tag))
            );
        }

        return filtered;
    };

    const filteredFAQs = getFilteredFAQs();

    // Event handlers
    const toggleFAQ = (index) => {
        setOpenIndex(openIndex === index ? null : index);
    };

    const toggleTag = (tag) => {
        setSelectedTags(prev => 
            prev.includes(tag) 
                ? prev.filter(t => t !== tag)
                : [...prev, tag]
        );
    };

    const clearAllFilters = () => {
        setSearchTerm("");
        setSelectedCategory("all");
        setSelectedTags([]);
    };

    // Loading state
    if (loading) {
        return (
            <AppLayout title="FAQ" currentPath="/faq">
                <Card sectioned>
                    <Box padding="600" textAlign="center">
                        <Spinner size="large" />
                        <Box paddingBlockStart="300">
                            <Text variant="bodyMd" tone="subdued">
                                Loading FAQ...
                            </Text>
                        </Box>
                    </Box>
                </Card>
            </AppLayout>
        );
    }

    // Error state
    if (error) {
        return (
            <AppLayout title="FAQ" currentPath="/faq">
                <Card sectioned>
                    <Box padding="600">
                        <EmptyState
                            heading="Failed to load FAQ"
                            image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                        >
                            <p>We couldn't load the FAQ data. Please try refreshing the page.</p>
                            <p>Error: {error}</p>
                        </EmptyState>
                    </Box>
                </Card>
            </AppLayout>
        );
    }

    return (
        <AppLayout title="Frequently Asked Questions" currentPath="/faq">
            {/* Header Section */}
            <Card sectioned>
                <Box padding="600" background="bg-surface-secondary" textAlign="center">
                    <BlockStack gap="400">
                        <Text variant="displayMd" as="h1">
                            Frequently Asked Questions
                        </Text>
                        <Text variant="bodyLg" tone="subdued">
                            Find answers to common questions about {faqConfig.appConfig?.appName || "this app"} setup and usage
                        </Text>
                    </BlockStack>
                </Box>
            </Card>

            {/* Search and Filter Section */}
            <Card sectioned>
                <Box padding="400">
                    <BlockStack gap="400">
                        {/* Search Bar */}
                        <TextField
                            label="Search FAQs"
                            value={searchTerm}
                            onChange={setSearchTerm}
                            placeholder="Search questions, answers, or tags..."
                            prefix={<Icon source={SearchIcon} />}
                            clearButton
                            onClearButtonClick={() => setSearchTerm("")}
                        />

                        {/* Filter Toggle */}
                        <InlineStack gap="300" align="space-between">
                            <Button
                                icon={FilterIcon}
                                onClick={() => setShowFilters(!showFilters)}
                                pressed={showFilters}
                                size="medium"
                            >
                                {showFilters ? 'Hide Filters' : 'Show Filters'}
                            </Button>
                            
                            {(searchTerm || selectedCategory !== 'all' || selectedTags.length > 0) && (
                                <Button onClick={clearAllFilters} size="medium">
                                    Clear All Filters
                                </Button>
                            )}
                        </InlineStack>

                        {/* Filter Section */}
                        <Collapsible open={showFilters}>
                            <Box padding="400" background="bg-surface-secondary" borderRadius="200">
                                <BlockStack gap="400">
                                    {/* Category Filter */}
                                    <Box>
                                        <Text variant="headingSm" as="h3" paddingBlockEnd="300">
                                            Categories
                                        </Text>
                                        <InlineStack gap="200" wrap={true}>
                                            <Button
                                                pressed={selectedCategory === "all"}
                                                onClick={() => setSelectedCategory("all")}
                                                size="medium"
                                            >
                                                All ({faqData.length})
                                            </Button>
                                            {Object.entries(faqConfig.categories || {}).map(([key, category]) => {
                                                const count = groupedFAQs[key]?.length || 0;
                                                return (
                                                    <Button
                                                        key={key}
                                                        pressed={selectedCategory === key}
                                                        onClick={() => setSelectedCategory(key)}
                                                        size="medium"
                                                    >
                                                        {category.icon} {category.title} ({count})
                                                    </Button>
                                                );
                                            })}
                                        </InlineStack>
                                    </Box>

                                    {/* Tag Filter */}
                                    <Box>
                                        <Text variant="headingSm" as="h3" paddingBlockEnd="300">
                                            Tags ({selectedTags.length} selected)
                                        </Text>
                                        <InlineStack gap="200" wrap={true}>
                                            {allTags.map(tag => (
                                                <Button
                                                    key={tag}
                                                    pressed={selectedTags.includes(tag)}
                                                    onClick={() => toggleTag(tag)}
                                                    size="small"
                                                >
                                                    {tag}
                                                </Button>
                                            ))}
                                        </InlineStack>
                                    </Box>
                                </BlockStack>
                            </Box>
                        </Collapsible>

                        {/* Results Summary */}
                        <Text variant="bodySm" tone="subdued">
                            Showing {filteredFAQs.length} of {faqData.length} questions
                            {searchTerm && ` matching "${searchTerm}"`}
                            {selectedCategory !== 'all' && ` in ${faqConfig.categories?.[selectedCategory]?.title || selectedCategory}`}
                            {selectedTags.length > 0 && ` with tags: ${selectedTags.join(', ')}`}
                        </Text>
                    </BlockStack>
                </Box>
            </Card>

            {/* FAQ Items */}
            <Card sectioned>
                <Box padding="400">
                    <BlockStack gap="300">
                        {filteredFAQs.length > 0 ? (
                            filteredFAQs.map((faq) => {
                                const isOpen = openIndex === faq.originalIndex || openIndex === faq.id;
                                const category = faqConfig.categories?.[faq.category] || { title: faq.category, icon: '❓', color: 'subdued' };

                                return (
                                    <Card key={faq.id || faq.originalIndex}>
                                        <Box
                                            padding="400"
                                            style={{ cursor: "pointer" }}
                                            onClick={() => toggleFAQ(faq.originalIndex || faq.id)}
                                        >
                                            <BlockStack gap="300">
                                                <InlineStack gap="300" align="space-between" blockAlign="start">
                                                    <Box style={{ flex: 1 }}>
                                                        <BlockStack gap="200">
                                                            <InlineStack gap="200" align="start" wrap={false}>
                                                                <Badge tone={category.color} size="small">
                                                                    {category.icon} {category.title}
                                                                </Badge>
                                                                {faq.priority && (
                                                                    <Badge 
                                                                        tone={faq.priority === 'high' ? 'critical' : faq.priority === 'medium' ? 'warning' : 'info'} 
                                                                        size="small"
                                                                    >
                                                                        {faq.priority}
                                                                    </Badge>
                                                                )}
                                                            </InlineStack>
                                                            <Text variant="headingMd" as="h3">
                                                                {faq.question}
                                                            </Text>
                                                            {faq.tags && (
                                                                <InlineStack gap="100" wrap={true}>
                                                                    {faq.tags.map((tag, tagIndex) => (
                                                                        <Button
                                                                            key={tagIndex}
                                                                            plain
                                                                            pressed={selectedTags.includes(tag)}
                                                                            size="micro"
                                                                            onClick={(e) => {
                                                                                e.stopPropagation();
                                                                                toggleTag(tag);
                                                                            }}
                                                                        >
                                                                            <Badge tone="subdued" size="small">
                                                                                {tag}
                                                                            </Badge>
                                                                        </Button>
                                                                    ))}
                                                                </InlineStack>
                                                            )}
                                                        </BlockStack>
                                                    </Box>
                                                    <Box>
                                                        <Icon source={isOpen ? ChevronUpIcon : ChevronDownIcon} />
                                                    </Box>
                                                </InlineStack>

                                                <Collapsible open={isOpen}>
                                                    <Box 
                                                        paddingBlockStart="400" 
                                                        paddingInlineStart="400"
                                                        borderInlineStartWidth="2"
                                                        borderColor="border-success"
                                                        background="bg-surface-secondary"
                                                        borderRadius="200"
                                                        padding="400"
                                                    >
                                                        <Text variant="bodyMd" color="subdued">
                                                            {faq.answer}
                                                        </Text>
                                                        {faq.lastUpdated && (
                                                            <Box paddingBlockStart="300">
                                                                <Text variant="caption" tone="subdued">
                                                                    Last updated: {new Date(faq.lastUpdated).toLocaleDateString()}
                                                                </Text>
                                                            </Box>
                                                        )}
                                                    </Box>
                                                </Collapsible>
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                );
                            })
                        ) : (
                            <Card sectioned>
                                <Box padding="400" textAlign="center">
                                    <EmptyState
                                        heading="No questions found"
                                        image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                                    >
                                        <p>Try adjusting your search terms or filters to find what you're looking for.</p>
                                        <Box paddingBlockStart="300">
                                            <Button onClick={clearAllFilters}>
                                                Clear All Filters
                                            </Button>
                                        </Box>
                                    </EmptyState>
                                </Box>
                            </Card>
                        )}
                    </BlockStack>
                </Box>
            </Card>

            {/* Help Section */}
            <Card sectioned>
                <Box padding="400" textAlign="center">
                    <BlockStack gap="400">
                        <Text variant="headingLg" as="h3">
                            Still Need Help?
                        </Text>
                        <Text variant="bodyMd" tone="subdued">
                            Can't find the answer you're looking for? Our support team is here to help you with {faqConfig.appConfig?.appName || "this app"}.
                        </Text>
                        <InlineStack gap="300" align="center">
                            <Button primary url="/support">
                                Contact Support
                            </Button>
                            <Button url="/docs">
                                View Documentation
                            </Button>
                        </InlineStack>
                    </BlockStack>
                </Box>
            </Card>
        </AppLayout>
    );
}