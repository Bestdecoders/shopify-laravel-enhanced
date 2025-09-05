import { useState, useCallback, useEffect } from "react";
import {
    Page,
    Card,
    BlockStack,
    InlineGrid,
    FormLayout,
    TextField,
    Button,
    Select,
    Checkbox,
    Banner,
    Spinner,
    Text,
    Box,
    Badge,
    Toast,
    Frame,
    Divider,
    ButtonGroup
} from "@shopify/polaris";
import { ArrowLeftIcon, SaveIcon, ResetIcon } from "@shopify/polaris-icons";
import { useAuthenticatedFetch } from "../hooks/useAuthenticatedFetch";
import ProductSelector from "../components/ProductSelector";
import CollectionSelector from "../components/CollectionSelector";

// =============================================================================
// ⚠️  SETTINGS PAGE - Generalized settings with validation and iterate items
// =============================================================================
// This component provides a comprehensive settings page with:
// - Basic form fields with validation
// - Product/Collection selection (iterate items)
// - Save/Reset functionality
// - Loading states and error handling
// =============================================================================

export default function Settings() {
    const fetch = useAuthenticatedFetch();
    
    // Form state
    const [formData, setFormData] = useState({
        appName: '',
        appDescription: '',
        primaryColor: '#000000',
        secondaryColor: '#ffffff',
        fontSize: '14',
        isEnabled: true,
        displayPosition: 'bottom',
        animationSpeed: '300',
        showOnMobile: true,
        autoHide: false,
        customCSS: '',
        selectedProducts: [],
        selectedCollections: [],
        targetingType: 'all' // all, products, collections, custom
    });
    
    // UI state
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});
    const [hasChanges, setHasChanges] = useState(false);
    const [toast, setToast] = useState({ active: false, message: '', error: false });
    const [showProductSelector, setShowProductSelector] = useState(false);
    const [showCollectionSelector, setShowCollectionSelector] = useState(false);
    
    // Load initial settings
    useEffect(() => {
        loadSettings();
    }, []);
    
    const loadSettings = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/settings');
            if (response.ok) {
                const data = await response.json();
                setFormData({ ...formData, ...data });
            }
        } catch (error) {
            showToast('Failed to load settings', true);
        } finally {
            setLoading(false);
        }
    };
    
    // Form validation
    const validateForm = () => {
        const newErrors = {};
        
        if (!formData.appName.trim()) {
            newErrors.appName = 'App name is required';
        } else if (formData.appName.length < 2) {
            newErrors.appName = 'App name must be at least 2 characters';
        }
        
        if (!formData.appDescription.trim()) {
            newErrors.appDescription = 'App description is required';
        } else if (formData.appDescription.length < 10) {
            newErrors.appDescription = 'Description must be at least 10 characters';
        }
        
        if (!formData.primaryColor.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
            newErrors.primaryColor = 'Please enter a valid hex color';
        }
        
        if (!formData.secondaryColor.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
            newErrors.secondaryColor = 'Please enter a valid hex color';
        }
        
        const fontSize = parseInt(formData.fontSize);
        if (!fontSize || fontSize < 10 || fontSize > 30) {
            newErrors.fontSize = 'Font size must be between 10 and 30 pixels';
        }
        
        const animationSpeed = parseInt(formData.animationSpeed);
        if (!animationSpeed || animationSpeed < 100 || animationSpeed > 2000) {
            newErrors.animationSpeed = 'Animation speed must be between 100 and 2000 milliseconds';
        }
        
        if (formData.targetingType === 'products' && formData.selectedProducts.length === 0) {
            newErrors.selectedProducts = 'Please select at least one product';
        }
        
        if (formData.targetingType === 'collections' && formData.selectedCollections.length === 0) {
            newErrors.selectedCollections = 'Please select at least one collection';
        }
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    
    // Handle form field changes
    const handleChange = useCallback((field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        setHasChanges(true);
        
        // Clear field-specific errors
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: undefined }));
        }
    }, [errors]);
    
    // Handle save
    const handleSave = async () => {
        if (!validateForm()) {
            showToast('Please fix the errors below', true);
            return;
        }
        
        try {
            setSaving(true);
            const response = await fetch('/api/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                setHasChanges(false);
                showToast('Settings saved successfully!');
            } else {
                throw new Error('Failed to save settings');
            }
        } catch (error) {
            showToast('Failed to save settings', true);
        } finally {
            setSaving(false);
        }
    };
    
    // Handle reset
    const handleReset = () => {
        loadSettings();
        setHasChanges(false);
        setErrors({});
        showToast('Settings reset to last saved values');
    };
    
    // Toast helper
    const showToast = (message, error = false) => {
        setToast({ active: true, message, error });
    };
    
    const hideToast = () => {
        setToast({ active: false, message: '', error: false });
    };
    
    // Product selection handlers
    const handleProductsSelected = (products) => {
        handleChange('selectedProducts', products);
        setShowProductSelector(false);
    };
    
    const handleCollectionsSelected = (collections) => {
        handleChange('selectedCollections', collections);
        setShowCollectionSelector(false);
    };
    
    const removeProduct = (productId) => {
        const updated = formData.selectedProducts.filter(p => p.id !== productId);
        handleChange('selectedProducts', updated);
    };
    
    const removeCollection = (collectionId) => {
        const updated = formData.selectedCollections.filter(c => c.id !== collectionId);
        handleChange('selectedCollections', updated);
    };
    
    // Options
    const displayPositionOptions = [
        { label: 'Top of page', value: 'top' },
        { label: 'Bottom of page', value: 'bottom' },
        { label: 'Before add to cart', value: 'before-cart' },
        { label: 'After add to cart', value: 'after-cart' }
    ];
    
    const targetingTypeOptions = [
        { label: 'All products', value: 'all' },
        { label: 'Specific products', value: 'products' },
        { label: 'Specific collections', value: 'collections' },
        { label: 'Custom rules', value: 'custom' }
    ];
    
    if (loading) {
        return (
            <Page title="Settings" fullWidth>
                <Box padding="600" textAlign="center">
                    <Spinner size="large" />
                    <Box paddingBlockStart="300">
                        <Text variant="bodyMd" tone="subdued">
                            Loading settings...
                        </Text>
                    </Box>
                </Box>
            </Page>
        );
    }
    
    return (
        <Frame>
            {toast.active && (
                <Toast
                    content={toast.message}
                    error={toast.error}
                    onDismiss={hideToast}
                    duration={4000}
                />
            )}
            
            <Page
                title="Settings"
                fullWidth
                backAction={{
                    content: 'Back to Dashboard',
                    url: '/home'
                }}
                primaryAction={{
                    content: saving ? 'Saving...' : 'Save Settings',
                    onAction: handleSave,
                    disabled: saving || !hasChanges,
                    loading: saving,
                    icon: SaveIcon
                }}
                secondaryActions={[
                    {
                        content: 'Reset Changes',
                        onAction: handleReset,
                        disabled: !hasChanges,
                        icon: ResetIcon
                    }
                ]}
            >
                <InlineGrid columns={{ xs: 1, lg: '2fr 1fr' }} gap="400">
                    {/* Main Settings */}
                    <BlockStack gap="400">
                        {/* Basic Information */}
                        <Card>
                            <BlockStack gap="400">
                                <Box padding="400">
                                    <Text variant="headingMd" as="h2">
                                        Basic Information
                                    </Text>
                                </Box>
                                <Divider />
                                <Box padding="400">
                                    <FormLayout>
                                        <TextField
                                            label="App Name"
                                            value={formData.appName}
                                            onChange={(value) => handleChange('appName', value)}
                                            error={errors.appName}
                                            placeholder="Enter your app name"
                                            requiredIndicator
                                        />
                                        <TextField
                                            label="App Description"
                                            value={formData.appDescription}
                                            onChange={(value) => handleChange('appDescription', value)}
                                            error={errors.appDescription}
                                            placeholder="Describe what your app does"
                                            multiline={3}
                                            requiredIndicator
                                        />
                                    </FormLayout>
                                </Box>
                            </BlockStack>
                        </Card>
                        
                        {/* Appearance Settings */}
                        <Card>
                            <BlockStack gap="400">
                                <Box padding="400">
                                    <Text variant="headingMd" as="h2">
                                        Appearance
                                    </Text>
                                </Box>
                                <Divider />
                                <Box padding="400">
                                    <FormLayout>
                                        <FormLayout.Group condensed>
                                            <TextField
                                                label="Primary Color"
                                                value={formData.primaryColor}
                                                onChange={(value) => handleChange('primaryColor', value)}
                                                error={errors.primaryColor}
                                                placeholder="#000000"
                                                prefix="#"
                                            />
                                            <TextField
                                                label="Secondary Color"
                                                value={formData.secondaryColor}
                                                onChange={(value) => handleChange('secondaryColor', value)}
                                                error={errors.secondaryColor}
                                                placeholder="#ffffff"
                                                prefix="#"
                                            />
                                        </FormLayout.Group>
                                        <FormLayout.Group>
                                            <TextField
                                                label="Font Size (px)"
                                                type="number"
                                                value={formData.fontSize}
                                                onChange={(value) => handleChange('fontSize', value)}
                                                error={errors.fontSize}
                                                min={10}
                                                max={30}
                                                suffix="px"
                                            />
                                            <TextField
                                                label="Animation Speed (ms)"
                                                type="number"
                                                value={formData.animationSpeed}
                                                onChange={(value) => handleChange('animationSpeed', value)}
                                                error={errors.animationSpeed}
                                                min={100}
                                                max={2000}
                                                suffix="ms"
                                            />
                                        </FormLayout.Group>
                                        <Select
                                            label="Display Position"
                                            options={displayPositionOptions}
                                            value={formData.displayPosition}
                                            onChange={(value) => handleChange('displayPosition', value)}
                                        />
                                        <TextField
                                            label="Custom CSS"
                                            value={formData.customCSS}
                                            onChange={(value) => handleChange('customCSS', value)}
                                            placeholder="/* Add custom CSS rules */"
                                            multiline={4}
                                        />
                                    </FormLayout>
                                </Box>
                            </BlockStack>
                        </Card>
                        
                        {/* Targeting Settings */}
                        <Card>
                            <BlockStack gap="400">
                                <Box padding="400">
                                    <Text variant="headingMd" as="h2">
                                        Targeting
                                    </Text>
                                </Box>
                                <Divider />
                                <Box padding="400">
                                    <FormLayout>
                                        <Select
                                            label="Show on"
                                            options={targetingTypeOptions}
                                            value={formData.targetingType}
                                            onChange={(value) => handleChange('targetingType', value)}
                                        />
                                        
                                        {formData.targetingType === 'products' && (
                                            <BlockStack gap="300">
                                                <ButtonGroup>
                                                    <Button
                                                        onClick={() => setShowProductSelector(true)}
                                                    >
                                                        Select Products
                                                    </Button>
                                                    {formData.selectedProducts.length > 0 && (
                                                        <Button
                                                            onClick={() => handleChange('selectedProducts', [])}
                                                            tone="critical"
                                                        >
                                                            Clear All
                                                        </Button>
                                                    )}
                                                </ButtonGroup>
                                                
                                                {errors.selectedProducts && (
                                                    <Banner tone="critical">
                                                        {errors.selectedProducts}
                                                    </Banner>
                                                )}
                                                
                                                {formData.selectedProducts.length > 0 && (
                                                    <BlockStack gap="200">
                                                        <Text variant="bodyMd" fontWeight="medium">
                                                            Selected Products ({formData.selectedProducts.length})
                                                        </Text>
                                                        {formData.selectedProducts.map((product) => (
                                                            <Box key={product.id}>
                                                                <Badge
                                                                    tone="info"
                                                                    onRemove={() => removeProduct(product.id)}
                                                                >
                                                                    {product.title}
                                                                </Badge>
                                                            </Box>
                                                        ))}
                                                    </BlockStack>
                                                )}
                                            </BlockStack>
                                        )}
                                        
                                        {formData.targetingType === 'collections' && (
                                            <BlockStack gap="300">
                                                <ButtonGroup>
                                                    <Button
                                                        onClick={() => setShowCollectionSelector(true)}
                                                    >
                                                        Select Collections
                                                    </Button>
                                                    {formData.selectedCollections.length > 0 && (
                                                        <Button
                                                            onClick={() => handleChange('selectedCollections', [])}
                                                            tone="critical"
                                                        >
                                                            Clear All
                                                        </Button>
                                                    )}
                                                </ButtonGroup>
                                                
                                                {errors.selectedCollections && (
                                                    <Banner tone="critical">
                                                        {errors.selectedCollections}
                                                    </Banner>
                                                )}
                                                
                                                {formData.selectedCollections.length > 0 && (
                                                    <BlockStack gap="200">
                                                        <Text variant="bodyMd" fontWeight="medium">
                                                            Selected Collections ({formData.selectedCollections.length})
                                                        </Text>
                                                        {formData.selectedCollections.map((collection) => (
                                                            <Box key={collection.id}>
                                                                <Badge
                                                                    tone="info"
                                                                    onRemove={() => removeCollection(collection.id)}
                                                                >
                                                                    {collection.title}
                                                                </Badge>
                                                            </Box>
                                                        ))}
                                                    </BlockStack>
                                                )}
                                            </BlockStack>
                                        )}
                                    </FormLayout>
                                </Box>
                            </BlockStack>
                                        )}
                                    </FormLayout>
                                </Box>
                            </BlockStack>
                        </Card>
                    </BlockStack>
                    
                    {/* Settings Summary */}
                    <BlockStack gap="400">
                        <Card>
                            <BlockStack gap="400">
                                <Box padding="400">
                                    <Text variant="headingMd" as="h2">
                                        Behavior
                                    </Text>
                                </Box>
                                <Divider />
                                <Box padding="400">
                                    <BlockStack gap="300">
                                        <Checkbox
                                            label="Enable app"
                                            checked={formData.isEnabled}
                                            onChange={(value) => handleChange('isEnabled', value)}
                                        />
                                        <Checkbox
                                            label="Show on mobile devices"
                                            checked={formData.showOnMobile}
                                            onChange={(value) => handleChange('showOnMobile', value)}
                                        />
                                        <Checkbox
                                            label="Auto-hide after interaction"
                                            checked={formData.autoHide}
                                            onChange={(value) => handleChange('autoHide', value)}
                                        />
                                    </BlockStack>
                                </Box>
                            </BlockStack>
                        </Card>
                        
                        <Card>
                            <BlockStack gap="400">
                                <Box padding="400">
                                    <Text variant="headingMd" as="h2">
                                        Quick Stats
                                    </Text>
                                </Box>
                                <Divider />
                                <Box padding="400">
                                    <BlockStack gap="300">
                                        <Box>
                                            <Text variant="bodyMd" tone="subdued">Status</Text>
                                            <Badge tone={formData.isEnabled ? "success" : "critical"}>
                                                {formData.isEnabled ? "Enabled" : "Disabled"}
                                            </Badge>
                                        </Box>
                                        <Box>
                                            <Text variant="bodyMd" tone="subdued">Targeting</Text>
                                            <Text variant="bodyMd">
                                                {targetingTypeOptions.find(opt => opt.value === formData.targetingType)?.label}
                                            </Text>
                                        </Box>
                                        {formData.targetingType === 'products' && (
                                            <Box>
                                                <Text variant="bodyMd" tone="subdued">Products Selected</Text>
                                                <Badge>{formData.selectedProducts.length}</Badge>
                                            </Box>
                                        )}
                                        {formData.targetingType === 'collections' && (
                                            <Box>
                                                <Text variant="bodyMd" tone="subdued">Collections Selected</Text>
                                                <Badge>{formData.selectedCollections.length}</Badge>
                                            </Box>
                                        )}
                                        <Box>
                                            <Text variant="bodyMd" tone="subdued">Changes</Text>
                                            <Badge tone={hasChanges ? "attention" : "success"}>
                                                {hasChanges ? "Unsaved" : "Saved"}
                                            </Badge>
                                        </Box>
                                    </BlockStack>
                                </Box>
                            </BlockStack>
                        </Card>
                    </BlockStack>
                </InlineGrid>
                
                {/* Product Selector Modal */}
                {showProductSelector && (
                    <ProductSelector
                        isOpen={showProductSelector}
                        onClose={() => setShowProductSelector(false)}
                        onProductsSelected={handleProductsSelected}
                        selectedProducts={formData.selectedProducts}
                        multiSelect={true}
                    />
                )}
                
                {/* Collection Selector Modal */}
                {showCollectionSelector && (
                    <CollectionSelector
                        isOpen={showCollectionSelector}
                        onClose={() => setShowCollectionSelector(false)}
                        onCollectionsSelected={handleCollectionsSelected}
                        selectedCollections={formData.selectedCollections}
                        multiSelect={true}
                    />
                )}
            </Page>
        </Frame>
    );
}