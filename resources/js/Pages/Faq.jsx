import React from 'react';
import { Page, InlineGrid, InlineStack, BlockStack, Card, Box, Text, Banner } from '@shopify/polaris';
import Sidebar from '../components/sidebar';
import FaqList from '../components/FaqList';

const Faq = ({ initialFaqs = [], categories = [], tags = [] }) => {
    return (
        <Page title="Frequently Asked Questions" fullWidth>
            {/* Two-column layout: Sidebar and FAQ Content */}
            <InlineGrid columns={{ xs: 1, lg: '300px 1fr' }} gap="400">
                {/* Sidebar - Fixed width on large screens */}
                <Box>
                    <Card>
                        <Sidebar />
                    </Card>
                </Box>

                {/* FAQ Content - Takes remaining space */}
                <Box>
                    <Card>
                        <Box padding="400">
                            <BlockStack gap="400">
                                {/* Page Header */}
                                <Banner>
                                    <Text variant="headingMd" as="h1">
                                        Frequently Asked Questions 💡
                                    </Text>
                                    <Text variant="bodyMd">
                                        Find answers to common questions about size charts, setup, customization, and troubleshooting. 
                                        Use the search and filters to quickly find what you're looking for.
                                    </Text>
                                </Banner>

                                {/* FAQ Statistics */}
                                <div style={{ 
                                    display: 'grid', 
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', 
                                    gap: '1rem' 
                                }}>
                                    <Card>
                                        <Box padding="300">
                                            <BlockStack gap="100">
                                                <Text variant="headingLg" as="h3" alignment="center">
                                                    {initialFaqs.length}
                                                </Text>
                                                <Text variant="bodyMd" tone="subdued" alignment="center">
                                                    Total FAQs
                                                </Text>
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                    
                                    <Card>
                                        <Box padding="300">
                                            <BlockStack gap="100">
                                                <Text variant="headingLg" as="h3" alignment="center">
                                                    {categories.length}
                                                </Text>
                                                <Text variant="bodyMd" tone="subdued" alignment="center">
                                                    Categories
                                                </Text>
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                    
                                    <Card>
                                        <Box padding="300">
                                            <BlockStack gap="100">
                                                <Text variant="headingLg" as="h3" alignment="center">
                                                    {tags.length}
                                                </Text>
                                                <Text variant="bodyMd" tone="subdued" alignment="center">
                                                    Topics
                                                </Text>
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                </div>

                                {/* Popular Categories */}
                                {categories.length > 0 && (
                                    <Card>
                                        <Box padding="400">
                                            <BlockStack gap="300">
                                                <Text variant="headingMd" as="h2">
                                                    Browse by Category
                                                </Text>
                                                <div style={{ 
                                                    display: 'grid', 
                                                    gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', 
                                                    gap: '0.75rem' 
                                                }}>
                                                    {categories.map((category) => {
                                                        const categoryCount = initialFaqs.filter(faq => faq.category === category).length;
                                                        return (
                                                            <Box 
                                                                key={category}
                                                                padding="300" 
                                                                background="bg-surface-secondary" 
                                                                borderRadius="200"
                                                                as="button"
                                                                onClick={() => {
                                                                    // This will trigger the filter in FaqList component
                                                                    const event = new CustomEvent('filterByCategory', { 
                                                                        detail: { category } 
                                                                    });
                                                                    window.dispatchEvent(event);
                                                                }}
                                                            >
                                                                <InlineStack align="space-between">
                                                                    <Text variant="bodyMd" as="span">
                                                                        {category}
                                                                    </Text>
                                                                    <Text variant="bodySm" tone="subdued" as="span">
                                                                        {categoryCount} FAQ{categoryCount !== 1 ? 's' : ''}
                                                                    </Text>
                                                                </InlineStack>
                                                            </Box>
                                                        );
                                                    })}
                                                </div>
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                )}

                                {/* Main FAQ List */}
                                <FaqList 
                                    initialFaqs={initialFaqs}
                                    categories={categories}
                                    tags={tags}
                                />
                            </BlockStack>
                        </Box>
                    </Card>
                </Box>
            </InlineGrid>
        </Page>
    );
};

export default Faq;