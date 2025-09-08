import React, { useState, useEffect, useMemo } from 'react';
import {
    Card,
    Text,
    BlockStack,
    InlineStack,
    Box,
    TextField,
    Select,
    Tag,
    Button,
    Badge,
    Collapsible,
    Icon,
    Spinner,
    EmptyState,
    Filters,
    ChoiceList,
} from '@shopify/polaris';
import {
    SearchIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    QuestionCircleIcon,
} from '@shopify/polaris-icons';
import { useAxios } from '../hooks/useAxios';

const FaqList = ({ initialFaqs = [], categories = [], tags = [] }) => {
    const [faqs, setFaqs] = useState(initialFaqs);
    const [loading, setLoading] = useState(false);
    const [searchValue, setSearchValue] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');
    const [selectedTags, setSelectedTags] = useState([]);
    const [selectedPriority, setSelectedPriority] = useState('');
    const [expandedItems, setExpandedItems] = useState(new Set());
    const [availableCategories, setAvailableCategories] = useState(categories);
    const [availableTags, setAvailableTags] = useState(tags);

    const axios = useAxios();

    // Debounced search effect
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (searchValue || selectedCategory || selectedTags.length > 0 || selectedPriority) {
                fetchFilteredFaqs();
            } else {
                setFaqs(initialFaqs);
            }
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [searchValue, selectedCategory, selectedTags, selectedPriority, initialFaqs]);

    const fetchFilteredFaqs = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (searchValue) params.append('search', searchValue);
            if (selectedCategory) params.append('category', selectedCategory);
            if (selectedPriority) params.append('priority', selectedPriority);
            selectedTags.forEach(tag => params.append('tag', tag));

            const response = await axios.get(`/faq/api/faqs?${params.toString()}`);
            setFaqs(response.data.faqs || []);
            setAvailableCategories(response.data.categories || []);
            setAvailableTags(response.data.tags || []);
        } catch (error) {
            console.error('Error fetching FAQs:', error);
        }
        setLoading(false);
    };

    const toggleExpanded = (id) => {
        const newExpandedItems = new Set(expandedItems);
        if (newExpandedItems.has(id)) {
            newExpandedItems.delete(id);
        } else {
            newExpandedItems.add(id);
        }
        setExpandedItems(newExpandedItems);
    };

    const clearAllFilters = () => {
        setSearchValue('');
        setSelectedCategory('');
        setSelectedTags([]);
        setSelectedPriority('');
        setFaqs(initialFaqs);
    };

    const handleTagRemove = (tagToRemove) => {
        setSelectedTags(selectedTags.filter(tag => tag !== tagToRemove));
    };

    const priorityOptions = [
        { label: 'All Priorities', value: '' },
        { label: 'High Priority', value: 'high' },
        { label: 'Medium Priority', value: 'medium' },
        { label: 'Low Priority', value: 'low' },
    ];

    const categoryOptions = [
        { label: 'All Categories', value: '' },
        ...availableCategories.map(cat => ({ label: cat, value: cat }))
    ];

    const getPriorityBadge = (priority) => {
        const tones = {
            high: 'critical',
            medium: 'warning', 
            low: 'success'
        };
        return <Badge tone={tones[priority] || 'info'}>{priority}</Badge>;
    };

    const appliedFilters = useMemo(() => {
        const filters = [];
        if (selectedCategory) {
            filters.push({
                key: 'category',
                label: `Category: ${selectedCategory}`,
                onRemove: () => setSelectedCategory(''),
            });
        }
        if (selectedPriority) {
            filters.push({
                key: 'priority',
                label: `Priority: ${selectedPriority}`,
                onRemove: () => setSelectedPriority(''),
            });
        }
        selectedTags.forEach(tag => {
            filters.push({
                key: `tag-${tag}`,
                label: `Tag: ${tag}`,
                onRemove: () => handleTagRemove(tag),
            });
        });
        return filters;
    }, [selectedCategory, selectedPriority, selectedTags]);

    const filterControl = (
        <Filters
            queryValue={searchValue}
            queryPlaceholder="Search FAQs..."
            onQueryChange={setSearchValue}
            onQueryClear={() => setSearchValue('')}
            onClearAll={clearAllFilters}
            appliedFilters={appliedFilters}
            filters={[
                {
                    key: 'category',
                    label: 'Category',
                    filter: (
                        <ChoiceList
                            title="Category"
                            titleHidden
                            choices={categoryOptions}
                            selected={selectedCategory ? [selectedCategory] : []}
                            onChange={(value) => setSelectedCategory(value[0] || '')}
                        />
                    ),
                    shortcut: true,
                },
                {
                    key: 'priority',
                    label: 'Priority',
                    filter: (
                        <ChoiceList
                            title="Priority"
                            titleHidden
                            choices={priorityOptions}
                            selected={selectedPriority ? [selectedPriority] : []}
                            onChange={(value) => setSelectedPriority(value[0] || '')}
                        />
                    ),
                    shortcut: true,
                },
                {
                    key: 'tags',
                    label: 'Tags',
                    filter: (
                        <ChoiceList
                            title="Tags"
                            titleHidden
                            allowMultiple
                            choices={availableTags.map(tag => ({ label: tag, value: tag }))}
                            selected={selectedTags}
                            onChange={setSelectedTags}
                        />
                    ),
                },
            ]}
        />
    );

    if (loading) {
        return (
            <Card>
                <Box padding="600">
                    <InlineStack align="center">
                        <Spinner accessibilityLabel="Loading FAQs" size="large" />
                        <Text variant="bodyMd">Loading FAQs...</Text>
                    </InlineStack>
                </Box>
            </Card>
        );
    }

    return (
        <BlockStack gap="400">
            {/* Search and Filters */}
            <Card>
                {filterControl}
            </Card>

            {/* Results Summary */}
            <Card>
                <Box padding="300">
                    <InlineStack align="space-between">
                        <Text variant="bodyMd" tone="subdued">
                            Showing {faqs.length} FAQ{faqs.length !== 1 ? 's' : ''}
                            {(searchValue || selectedCategory || selectedTags.length > 0 || selectedPriority) && 
                             ` matching your criteria`}
                        </Text>
                        {appliedFilters.length > 0 && (
                            <Button size="slim" onClick={clearAllFilters}>
                                Clear all filters
                            </Button>
                        )}
                    </InlineStack>
                </Box>
            </Card>

            {/* FAQ Items */}
            {faqs.length === 0 ? (
                <Card>
                    <EmptyState
                        heading="No FAQs found"
                        description="Try adjusting your search terms or filters to find what you're looking for."
                        image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                        action={{
                            content: 'Clear filters',
                            onAction: clearAllFilters,
                        }}
                    />
                </Card>
            ) : (
                <BlockStack gap="300">
                    {faqs.map((faq) => {
                        const isExpanded = expandedItems.has(faq.id);
                        return (
                            <Card key={faq.id}>
                                <Box padding="400">
                                    <BlockStack gap="300">
                                        {/* Question Header */}
                                        <Button
                                            fullWidth
                                            textAlign="left"
                                            disclosure={isExpanded ? 'up' : 'down'}
                                            onClick={() => toggleExpanded(faq.id)}
                                        >
                                            <InlineStack align="space-between" blockAlign="center">
                                                <InlineStack gap="200" blockAlign="center">
                                                    <Icon source={QuestionCircleIcon} tone="base" />
                                                    <Text variant="headingSm" as="h3">
                                                        {faq.question}
                                                    </Text>
                                                </InlineStack>
                                                <InlineStack gap="200">
                                                    {getPriorityBadge(faq.priority)}
                                                    <Badge tone="info">{faq.category}</Badge>
                                                </InlineStack>
                                            </InlineStack>
                                        </Button>

                                        {/* Answer Content */}
                                        <Collapsible
                                            open={isExpanded}
                                            id={`faq-${faq.id}`}
                                            transition={{
                                                duration: '200ms',
                                                timingFunction: 'ease-in-out',
                                            }}
                                        >
                                            <Box paddingBlockStart="300">
                                                <BlockStack gap="300">
                                                    <Box
                                                        padding="300"
                                                        background="bg-surface-secondary"
                                                        borderRadius="200"
                                                    >
                                                        <Text variant="bodyMd">
                                                            {faq.answer}
                                                        </Text>
                                                    </Box>
                                                    
                                                    {/* Tags */}
                                                    {faq.tags && faq.tags.length > 0 && (
                                                        <InlineStack gap="200" wrap>
                                                            <Text variant="bodySm" tone="subdued">
                                                                Tags:
                                                            </Text>
                                                            {faq.tags.map((tag) => (
                                                                <Tag
                                                                    key={tag}
                                                                    onClick={() => {
                                                                        if (!selectedTags.includes(tag)) {
                                                                            setSelectedTags([...selectedTags, tag]);
                                                                        }
                                                                    }}
                                                                >
                                                                    {tag}
                                                                </Tag>
                                                            ))}
                                                        </InlineStack>
                                                    )}
                                                </BlockStack>
                                            </Box>
                                        </Collapsible>
                                    </BlockStack>
                                </Box>
                            </Card>
                        );
                    })}
                </BlockStack>
            )}
        </BlockStack>
    );
};

export default FaqList;