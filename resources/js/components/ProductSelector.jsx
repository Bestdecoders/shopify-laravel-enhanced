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
import { SearchIcon, ProductIcon } from "@shopify/polaris-icons";
import { useAuthenticatedFetch } from "../hooks/useAuthenticatedFetch";

// =============================================================================
// ⚠️  PRODUCT SELECTOR - Modal for selecting products with search and pagination
// =============================================================================
// This component provides a comprehensive product selection modal with:
// - Search functionality
// - Pagination for large product lists
// - Multi-select with checkboxes
// - Selected products management
// - Loading states and error handling
// =============================================================================

export default function ProductSelector({
    isOpen,
    onClose,
    onProductsSelected,
    selectedProducts = [],
    multiSelect = true,
    title = "Select Products"
}) {
    const fetch = useAuthenticatedFetch();
    
    // State management
    const [searchQuery, setSearchQuery] = useState("");
    const [products, setProducts] = useState([]);
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
            const selectedIds = new Set(selectedProducts.map(p => p.id));
            setSelectedItems(selectedIds);
            loadProducts();
        }
    }, [isOpen, selectedProducts]);
    
    // Load products with search and pagination
    const loadProducts = useCallback(async (searchTerm = searchQuery, pageDirection = 'next', pageCursor = null) => {
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
            
            // Include currently selected products to prioritize them
            const selectedIds = Array.from(selectedItems);
            if (selectedIds.length > 0 && !pageCursor) {
                params.append('selectedItems', selectedIds.join(','));
            }
            
            const response = await fetch(`/api/products?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.edges) {
                setProducts(data.edges.map(edge => edge.node));
                setHasNextPage(data.pageInfo?.hasNextPage || false);
                setHasPreviousPage(data.pageInfo?.hasPreviousPage || false);
                
                // Update cursors for pagination
                if (data.edges.length > 0) {
                    setCursor(data.pageInfo?.endCursor || null);
                }
            } else {
                setProducts([]);
                setHasNextPage(false);
                setHasPreviousPage(false);
            }
        } catch (err) {
            console.error('Error loading products:', err);
            setError('Failed to load products. Please try again.');
            setProducts([]);
        } finally {
            setLoading(false);
        }
    }, [fetch, searchQuery, selectedItems]);
    
    // Handle search
    const handleSearch = useCallback((value) => {
        setSearchQuery(value);
        setCursor(null);
        setCurrentPage(1);
        loadProducts(value, 'next', null);
    }, [loadProducts]);
    
    // Handle pagination
    const handleNextPage = () => {
        if (hasNextPage && cursor) {
            setCurrentPage(prev => prev + 1);
            setDirection('next');
            loadProducts(searchQuery, 'next', cursor);
        }
    };
    
    const handlePreviousPage = () => {
        if (hasPreviousPage) {
            setCurrentPage(prev => Math.max(1, prev - 1));
            setDirection('previous');
            loadProducts(searchQuery, 'previous', cursor);
        }
    };
    
    // Handle item selection
    const handleItemSelect = (productId, product) => {
        const newSelection = new Set(selectedItems);
        
        if (newSelection.has(productId)) {
            newSelection.delete(productId);
        } else {
            if (!multiSelect) {
                newSelection.clear();
            }
            newSelection.add(productId);
        }
        
        setSelectedItems(newSelection);
    };
    
    // Handle select all visible
    const handleSelectAllVisible = () => {
        const visibleProductIds = products.map(p => p.id);
        const newSelection = new Set(selectedItems);
        
        const allVisibleSelected = visibleProductIds.every(id => newSelection.has(id));
        
        if (allVisibleSelected) {
            // Deselect all visible
            visibleProductIds.forEach(id => newSelection.delete(id));
        } else {
            // Select all visible
            visibleProductIds.forEach(id => newSelection.add(id));
        }
        
        setSelectedItems(newSelection);
    };
    
    // Handle confirm selection
    const handleConfirm = () => {
        const selectedProductsData = [];
        
        // Include previously selected products that aren't in current view
        selectedProducts.forEach(product => {
            if (selectedItems.has(product.id)) {
                selectedProductsData.push(product);
            }
        });
        
        // Add newly selected products from current view
        products.forEach(product => {
            if (selectedItems.has(product.id) && !selectedProductsData.find(p => p.id === product.id)) {
                selectedProductsData.push({
                    id: product.id,
                    title: product.title,
                    handle: product.handle,
                    featuredImage: product.featuredImage
                });
            }
        });
        
        onProductsSelected(selectedProductsData);
    };
    
    // Handle clear all
    const handleClearAll = () => {
        setSelectedItems(new Set());
    };
    
    // Close handler
    const handleClose = () => {
        setSearchQuery("");
        setProducts([]);
        setCursor(null);
        setCurrentPage(1);
        setError(null);
        onClose();
    };
    
    // Render product item
    const renderProductItem = (product) => {
        const isSelected = selectedItems.has(product.id);
        const media = product.featuredImage ? (
            <Avatar
                source={product.featuredImage.url}
                alt={product.title}
                size="medium"
            />
        ) : (
            <Avatar
                source=""
                alt={product.title}
                size="medium"
            />
        );
        
        return (
            <ResourceItem
                id={product.id}
                media={media}
                accessibilityLabel={`Select ${product.title}`}
                onClick={() => handleItemSelect(product.id, product)}
            >
                <Box>
                    <InlineStack align="space-between" blockAlign="center">
                        <BlockStack gap="100">
                            <Text variant="bodyMd" fontWeight="medium">
                                {product.title}
                            </Text>
                            <Text variant="bodySm" tone="subdued">
                                Handle: {product.handle}
                            </Text>
                            {product.vendor && (
                                <Text variant="caption" tone="subdued">
                                    Vendor: {product.vendor}
                                </Text>
                            )}
                        </BlockStack>
                        <Checkbox
                            checked={isSelected}
                            onChange={() => handleItemSelect(product.id, product)}
                        />
                    </InlineStack>
                </Box>
            </ResourceItem>
        );
    };
    
    const selectedCount = selectedItems.size;
    const visibleSelectedCount = products.filter(p => selectedItems.has(p.id)).length;
    
    return (
        <Modal
            open={isOpen}
            onClose={handleClose}
            title={title}
            primaryAction={{
                content: multiSelect ? `Select ${selectedCount} Product${selectedCount === 1 ? '' : 's'}` : 'Select Product',
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
                                    label="Search products"
                                    value={searchQuery}
                                    onChange={handleSearch}
                                    placeholder="Search by product name, handle, or vendor..."
                                    prefix={<SearchIcon />}
                                    clearButton
                                    onClearButtonClick={() => handleSearch("")}
                                />
                                
                                {multiSelect && products.length > 0 && (
                                    <InlineStack align="space-between" blockAlign="center">
                                        <ButtonGroup>
                                            <Button
                                                onClick={handleSelectAllVisible}
                                                size="slim"
                                            >
                                                {visibleSelectedCount === products.length ? 'Deselect All Visible' : 'Select All Visible'}
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
                    
                    {/* Products List */}
                    <Card>
                        {loading ? (
                            <Box padding="600" textAlign="center">
                                <Spinner size="small" />
                                <Box paddingBlockStart="200">
                                    <Text variant="bodySm" tone="subdued">
                                        Loading products...
                                    </Text>
                                </Box>
                            </Box>
                        ) : products.length === 0 ? (
                            <EmptyState
                                heading="No products found"
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                            >
                                <Text variant="bodyMd">
                                    {searchQuery ? `No products found matching "${searchQuery}"` : 'No products available'}
                                </Text>
                            </EmptyState>
                        ) : (
                            <ResourceList
                                resourceName={{ singular: 'product', plural: 'products' }}
                                items={products}
                                renderItem={renderProductItem}
                                totalItemsCount={products.length}
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