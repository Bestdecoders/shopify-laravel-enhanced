import { useState, useCallback, useEffect } from "react";
import {
    Modal,
    TextField,
    Button,
    ResourceList,
    ResourceItem,
    Avatar,
    Text,
    Box,
    BlockStack,
    InlineStack,
    Checkbox,
    Badge,
    Spinner,
    EmptyState,
    Pagination,
    ButtonGroup,
    Card,
    Banner
} from "@shopify/polaris";
import { SearchIcon, CollectionIcon } from "@shopify/polaris-icons";
import { useAuthenticatedFetch } from "../hooks/useAuthenticatedFetch";

// =============================================================================
// ⚠️  COLLECTION SELECTOR - Modal for selecting collections with search and pagination
// =============================================================================
// This component provides a comprehensive collection selection modal with:
// - Search functionality
// - Pagination for large collection lists
// - Multi-select with checkboxes
// - Selected collections management
// - Loading states and error handling
// =============================================================================

export default function CollectionSelector({
    isOpen,
    onClose,
    onCollectionsSelected,
    selectedCollections = [],
    multiSelect = true,
    title = "Select Collections"
}) {
    const fetch = useAuthenticatedFetch();
    
    // State management
    const [searchQuery, setSearchQuery] = useState("");
    const [collections, setCollections] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [selectedItems, setSelectedItems] = useState(new Set());
    const [currentPage, setCurrentPage] = useState(1);
    const [hasNextPage, setHasNextPage] = useState(false);
    const [hasPreviousPage, setHasPreviousPage] = useState(false);
    const [cursor, setCursor] = useState(null);
    const [direction, setDirection] = useState('next');
    
    // Initialize selected items when modal opens
    useEffect(() => {
        if (isOpen) {
            const selectedIds = new Set(selectedCollections.map(c => c.id));
            setSelectedItems(selectedIds);
            loadCollections();
        }
    }, [isOpen, selectedCollections]);
    
    // Load collections with search and pagination
    const loadCollections = useCallback(async (searchTerm = searchQuery, pageDirection = 'next', pageCursor = null) => {
        try {
            setLoading(true);
            setError(null);
            
            const params = new URLSearchParams({
                shop: window.location.hostname,
                query: searchTerm,
                direction: pageDirection,
                size: '10'
            });
            
            if (pageCursor) {
                params.append('cursor', pageCursor);
            }
            
            const response = await fetch(`/api/collections?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.edges) {
                setCollections(data.edges.map(edge => edge.node));
                setHasNextPage(data.pageInfo?.hasNextPage || false);
                setHasPreviousPage(data.pageInfo?.hasPreviousPage || false);
                
                // Update cursors for pagination
                if (data.edges.length > 0) {
                    setCursor(data.pageInfo?.endCursor || null);
                }
            } else {
                setCollections([]);
                setHasNextPage(false);
                setHasPreviousPage(false);
            }
        } catch (err) {
            console.error('Error loading collections:', err);
            setError('Failed to load collections. Please try again.');
            setCollections([]);
        } finally {
            setLoading(false);
        }
    }, [fetch, searchQuery]);
    
    // Handle search
    const handleSearch = useCallback((value) => {
        setSearchQuery(value);
        setCursor(null);
        setCurrentPage(1);
        loadCollections(value, 'next', null);
    }, [loadCollections]);
    
    // Handle pagination
    const handleNextPage = () => {
        if (hasNextPage && cursor) {
            setCurrentPage(prev => prev + 1);
            setDirection('next');
            loadCollections(searchQuery, 'next', cursor);
        }
    };
    
    const handlePreviousPage = () => {
        if (hasPreviousPage) {
            setCurrentPage(prev => Math.max(1, prev - 1));
            setDirection('previous');
            loadCollections(searchQuery, 'previous', cursor);
        }
    };
    
    // Handle item selection
    const handleItemSelect = (collectionId, collection) => {
        const newSelection = new Set(selectedItems);
        
        if (newSelection.has(collectionId)) {
            newSelection.delete(collectionId);
        } else {
            if (!multiSelect) {
                newSelection.clear();
            }
            newSelection.add(collectionId);
        }
        
        setSelectedItems(newSelection);
    };
    
    // Handle select all visible
    const handleSelectAllVisible = () => {
        const visibleCollectionIds = collections.map(c => c.id);
        const newSelection = new Set(selectedItems);
        
        const allVisibleSelected = visibleCollectionIds.every(id => newSelection.has(id));
        
        if (allVisibleSelected) {
            // Deselect all visible
            visibleCollectionIds.forEach(id => newSelection.delete(id));
        } else {
            // Select all visible
            visibleCollectionIds.forEach(id => newSelection.add(id));
        }
        
        setSelectedItems(newSelection);
    };
    
    // Handle confirm selection
    const handleConfirm = () => {
        const selectedCollectionsData = [];
        
        // Include previously selected collections that aren't in current view
        selectedCollections.forEach(collection => {
            if (selectedItems.has(collection.id)) {
                selectedCollectionsData.push(collection);
            }
        });
        
        // Add newly selected collections from current view
        collections.forEach(collection => {
            if (selectedItems.has(collection.id) && !selectedCollectionsData.find(c => c.id === collection.id)) {
                selectedCollectionsData.push({
                    id: collection.id,
                    title: collection.title,
                    handle: collection.handle,
                    image: collection.image,
                    productsCount: collection.productsCount
                });
            }
        });
        
        onCollectionsSelected(selectedCollectionsData);
    };
    
    // Handle clear all
    const handleClearAll = () => {
        setSelectedItems(new Set());
    };
    
    // Close handler
    const handleClose = () => {
        setSearchQuery("");
        setCollections([]);
        setCursor(null);
        setCurrentPage(1);
        setError(null);
        onClose();
    };
    
    // Render collection item
    const renderCollectionItem = (collection) => {
        const isSelected = selectedItems.has(collection.id);
        const media = collection.image ? (
            <Avatar
                source={collection.image.url}
                alt={collection.title}
                size="medium"
            />
        ) : (
            <Avatar
                source=""
                alt={collection.title}
                size="medium"
            />
        );
        
        return (
            <ResourceItem
                id={collection.id}
                media={media}
                accessibilityLabel={`Select ${collection.title}`}
                onClick={() => handleItemSelect(collection.id, collection)}
            >
                <Box>
                    <InlineStack align="space-between" blockAlign="center">
                        <BlockStack gap="100">
                            <Text variant="bodyMd" fontWeight="medium">
                                {collection.title}
                            </Text>
                            <Text variant="bodySm" tone="subdued">
                                Handle: {collection.handle}
                            </Text>
                            {collection.productsCount !== undefined && (
                                <Text variant="caption" tone="subdued">
                                    Products: {collection.productsCount.count || 0}
                                </Text>
                            )}
                            {collection.description && (
                                <Text variant="caption" tone="subdued">
                                    {collection.description.substring(0, 100)}
                                    {collection.description.length > 100 ? '...' : ''}
                                </Text>
                            )}
                        </BlockStack>
                        <Checkbox
                            checked={isSelected}
                            onChange={() => handleItemSelect(collection.id, collection)}
                        />
                    </InlineStack>
                </Box>
            </ResourceItem>
        );
    };
    
    const selectedCount = selectedItems.size;
    const visibleSelectedCount = collections.filter(c => selectedItems.has(c.id)).length;
    
    return (
        <Modal
            open={isOpen}
            onClose={handleClose}
            title={title}
            primaryAction={{
                content: multiSelect ? `Select ${selectedCount} Collection${selectedCount === 1 ? '' : 's'}` : 'Select Collection',
                onAction: handleConfirm,
                disabled: selectedCount === 0
            }}
            secondaryActions={[
                {
                    content: 'Cancel',
                    onAction: handleClose
                }
            ]}
            size="large"
        >
            <Modal.Section>
                <BlockStack gap="400">
                    {/* Search and Actions */}
                    <Card>
                        <Box padding="400">
                            <BlockStack gap="300">
                                <TextField
                                    label="Search collections"
                                    value={searchQuery}
                                    onChange={handleSearch}
                                    placeholder="Search by collection name, handle, or description..."
                                    prefix={<SearchIcon />}
                                    clearButton
                                    onClearButtonClick={() => handleSearch("")}
                                />
                                
                                {multiSelect && collections.length > 0 && (
                                    <InlineStack align="space-between" blockAlign="center">
                                        <ButtonGroup>
                                            <Button
                                                onClick={handleSelectAllVisible}
                                                size="slim"
                                            >
                                                {visibleSelectedCount === collections.length ? 'Deselect All Visible' : 'Select All Visible'}
                                            </Button>
                                            {selectedCount > 0 && (
                                                <Button
                                                    onClick={handleClearAll}
                                                    tone="critical"
                                                    size="slim"
                                                >
                                                    Clear All
                                                </Button>
                                            )}
                                        </ButtonGroup>
                                        
                                        {selectedCount > 0 && (
                                            <Badge tone="info">
                                                {selectedCount} selected
                                            </Badge>
                                        )}
                                    </InlineStack>
                                )}
                            </BlockStack>
                        </Box>
                    </Card>
                    
                    {/* Error Banner */}
                    {error && (
                        <Banner tone="critical">
                            {error}
                        </Banner>
                    )}
                    
                    {/* Collections List */}
                    <Card>
                        {loading ? (
                            <Box padding="600" textAlign="center">
                                <Spinner size="small" />
                                <Box paddingBlockStart="200">
                                    <Text variant="bodySm" tone="subdued">
                                        Loading collections...
                                    </Text>
                                </Box>
                            </Box>
                        ) : collections.length === 0 ? (
                            <EmptyState
                                heading="No collections found"
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                            >
                                <Text variant="bodyMd">
                                    {searchQuery ? `No collections found matching "${searchQuery}"` : 'No collections available'}
                                </Text>
                            </EmptyState>
                        ) : (
                            <ResourceList
                                resourceName={{ singular: 'collection', plural: 'collections' }}
                                items={collections}
                                renderItem={renderCollectionItem}
                                totalItemsCount={collections.length}
                                showHeader
                            />
                        )}
                    </Card>
                    
                    {/* Pagination */}
                    {(hasNextPage || hasPreviousPage) && (
                        <Box textAlign="center" padding="400">
                            <Pagination
                                hasPrevious={hasPreviousPage}
                                onPrevious={handlePreviousPage}
                                hasNext={hasNextPage}
                                onNext={handleNextPage}
                                label={`Page ${currentPage}`}
                            />
                        </Box>
                    )}
                </BlockStack>
            </Modal.Section>
        </Modal>
    );
}