import React from 'react';
import { Card, Box, Text, TextField, Button, BlockStack, Badge } from '@shopify/polaris';
import { SearchIcon } from '@shopify/polaris-icons';

const SearchBar = ({ 
    searchQuery, 
    onSearchChange, 
    onSearch, 
    searchResults, 
    showSearchResults, 
    onResultClick 
}) => {
    return (
        <Card background="bg-surface-secondary">
            <Box padding="400">
                <div style={{ position: 'relative' }}>
                    <TextField
                        value={searchQuery}
                        onChange={onSearchChange}
                        placeholder="Type to search documentation..."
                        prefix={<SearchIcon />}
                        onKeyPress={(e) => {
                            if (e.key === 'Enter') {
                                onSearch();
                            }
                        }}
                    />
                    
                    {/* Floating Search Results */}
                    {showSearchResults && searchResults.length > 0 && (
                        <div style={{
                            position: 'relative',
                            top: '100%',
                            left: 0,
                            right: 0,
                            zIndex: 1001, // Higher than TOC (5) to bring above
                            marginTop: '8px'
                        }}>
                            <Card>
                                <Box padding="300">
                                    <BlockStack gap="200">
                                        <Text variant="headingSm" as="h3">
                                            🔍 Search Results ({searchResults.length})
                                        </Text>
                                        <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
                                            <BlockStack gap="100">
                                                {searchResults.map((result, index) => (
                                                    <Button
                                                        key={index}
                                                        variant="plain"
                                                        textAlign="start"
                                                        onClick={() => onResultClick(result.id)}
                                                        style={{
                                                            width: '100%',
                                                            justifyContent: 'flex-start',
                                                            padding: '12px'
                                                        }}
                                                    >
                                                        <BlockStack gap="100">
                                                            <Text variant="bodyMd" fontWeight="semibold">
                                                                {result.title}
                                                            </Text>
                                                            <Text variant="bodySm" tone="subdued">
                                                                {result.excerpt}...
                                                            </Text>
                                                            <Badge tone="info" size="small">{result.category}</Badge>
                                                        </BlockStack>
                                                    </Button>
                                                ))}
                                            </BlockStack>
                                        </div>
                                    </BlockStack>
                                </Box>
                            </Card>
                        </div>
                    )}
                </div>
            </Box>
        </Card>
    );
};

export default SearchBar;