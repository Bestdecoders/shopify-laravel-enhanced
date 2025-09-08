import React from 'react';
import { Card, Box, Text, Button, BlockStack } from '@shopify/polaris';

const TableOfContents = ({ toc, activeSection, onSectionClick }) => {
    if (!toc || toc.length === 0) return null;

    return (
        <div style={{ 
            position: 'fixed', 
            top: '240px', // Moved further down to avoid covering search bar
            right: '20px', 
            width: '280px',
            maxHeight: '500px',
            zIndex: 5, // Lower than search results
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
        }}>
            <Card>
                <Box padding="400">
                    <BlockStack gap="300">
                        <Text variant="headingMd" as="h3" style={{ fontSize: '1.1rem', fontWeight: '600' }}>
                            📋 Table of Contents
                        </Text>
                        
                        <div style={{ 
                            maxHeight: '400px', 
                            overflowY: 'auto',
                            paddingRight: '8px'
                        }}>
                            <BlockStack gap="100">
                                {toc.filter(item => item.level <= 3).map((item, index) => (
                                    <Button
                                        key={index}
                                        variant="plain"
                                        size="slim"
                                        textAlign="start"
                                        onClick={() => onSectionClick(item.slug)}
                                        pressed={activeSection === item.slug}
                                        style={{
                                            paddingLeft: `${(item.level - 1) * 16 + 12}px`,
                                            paddingTop: '10px',
                                            paddingBottom: '10px',
                                            fontSize: item.level <= 2 ? '0.95rem' : '0.85rem',
                                            fontWeight: item.level <= 2 ? '600' : '500',
                                            width: '100%',
                                            justifyContent: 'flex-start',
                                            borderRadius: '6px',
                                            transition: 'all 0.2s ease'
                                        }}
                                    >
                                        {item.title}
                                    </Button>
                                ))}
                            </BlockStack>
                        </div>
                    </BlockStack>
                </Box>
            </Card>
        </div>
    );
};

export default TableOfContents;