import { useState, useEffect, useCallback } from "react";
import {
    Modal,
    FormLayout,
    TextField,
    Select,
    Checkbox,
    TextArea,
    Button,
    BlockStack,
    InlineStack,
    Card,
    Box,
    Text,
    Banner,
    Spinner,
    Badge,
    ButtonGroup
} from "@shopify/polaris";
import { SaveIcon, ResetIcon } from "@shopify/polaris-icons";
import ProductSelector from "./ProductSelector";
import CollectionSelector from "./CollectionSelector";

// =============================================================================
// ⚠️  DATA FORM - Generalized form for creating/editing data entities
// =============================================================================
// This component provides a flexible form interface that can handle any
// data structure with validation, product/collection selection, and more
// =============================================================================

export default function DataForm({
    isOpen = false,
    mode = 'create', // 'create', 'edit', 'view'
    item = null,
    onSave,
    onClose,
    entityType = "item",
    entitySingular = "Item",
    config = {}
}) {
    const [formData, setFormData] = useState({});
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);
    const [showProductSelector, setShowProductSelector] = useState(false);
    const [showCollectionSelector, setShowCollectionSelector] = useState(false);
    
    // Form fields configuration
    const formFields = config.formFields || [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            required: true,
            placeholder: `Enter ${entitySingular.toLowerCase()} title`
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'Description',
            placeholder: `Enter ${entitySingular.toLowerCase()} description`
        },
        {
            key: 'status',
            type: 'select',
            label: 'Status',
            options: [
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' },
                { label: 'Draft', value: 'draft' }
            ],
            required: true
        }
    ];
    
    // Initialize form data
    useEffect(() => {
        if (isOpen) {
            if (mode === 'edit' && item) {
                setFormData({ ...item });
            } else {
                // Initialize with default values
                const defaultData = {};
                formFields.forEach(field => {
                    if (field.defaultValue !== undefined) {
                        defaultData[field.key] = field.defaultValue;
                    } else {
                        switch (field.type) {
                            case 'boolean':
                            case 'checkbox':
                                defaultData[field.key] = false;
                                break;
                            case 'array':
                                defaultData[field.key] = [];
                                break;
                            case 'object':
                                defaultData[field.key] = {};
                                break;
                            default:
                                defaultData[field.key] = '';
                        }
                    }
                });
                setFormData(defaultData);
            }
            setErrors({});
        }
    }, [isOpen, mode, item, formFields]);
    
    // Handle field changes
    const handleChange = useCallback((field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        
        // Clear field error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: undefined }));
        }
    }, [errors]);
    
    // Validate form
    const validateForm = useCallback(() => {
        const newErrors = {};
        
        formFields.forEach(field => {
            const value = formData[field.key];
            
            // Required field validation
            if (field.required) {
                if (value === undefined || value === null || value === '' || 
                    (Array.isArray(value) && value.length === 0)) {
                    newErrors[field.key] = `${field.label} is required`;
                }
            }
            
            // Type-specific validation
            if (value) {
                switch (field.type) {
                    case 'email':
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            newErrors[field.key] = 'Please enter a valid email address';
                        }
                        break;
                    case 'url':
                        try {
                            new URL(value);
                        } catch {
                            newErrors[field.key] = 'Please enter a valid URL';
                        }
                        break;
                    case 'number':
                        if (isNaN(Number(value))) {
                            newErrors[field.key] = 'Please enter a valid number';
                        } else {
                            if (field.min !== undefined && Number(value) < field.min) {
                                newErrors[field.key] = `Value must be at least ${field.min}`;
                            }
                            if (field.max !== undefined && Number(value) > field.max) {
                                newErrors[field.key] = `Value must be at most ${field.max}`;
                            }
                        }
                        break;
                    case 'text':
                    case 'textarea':
                        if (field.minLength && value.length < field.minLength) {
                            newErrors[field.key] = `Must be at least ${field.minLength} characters`;
                        }
                        if (field.maxLength && value.length > field.maxLength) {
                            newErrors[field.key] = `Must be at most ${field.maxLength} characters`;
                        }
                        break;
                }
            }
            
            // Custom validation
            if (field.validate && typeof field.validate === 'function') {
                const customError = field.validate(value, formData);
                if (customError) {
                    newErrors[field.key] = customError;
                }
            }
        });
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    }, [formData, formFields]);
    
    // Handle save
    const handleSave = useCallback(async () => {
        if (!validateForm()) {
            return;
        }
        
        setSaving(true);
        try {
            await onSave(formData);
        } catch (error) {
            console.error('Save error:', error);
        } finally {
            setSaving(false);
        }
    }, [formData, validateForm, onSave]);
    
    // Handle reset
    const handleReset = useCallback(() => {
        if (mode === 'edit' && item) {
            setFormData({ ...item });
        } else {
            const defaultData = {};
            formFields.forEach(field => {
                defaultData[field.key] = field.defaultValue || '';
            });
            setFormData(defaultData);
        }
        setErrors({});
    }, [mode, item, formFields]);
    
    // Render form field
    const renderField = useCallback((field) => {
        const value = formData[field.key] || '';
        const error = errors[field.key];
        const disabled = mode === 'view' || field.disabled;
        
        switch (field.type) {
            case 'text':
            case 'email':
            case 'url':
            case 'password':
                return (
                    <TextField
                        key={field.key}
                        label={field.label}
                        type={field.type}
                        value={value}
                        onChange={(val) => handleChange(field.key, val)}
                        error={error}
                        placeholder={field.placeholder}
                        disabled={disabled}
                        required={field.required}
                        helpText={field.helpText}
                        prefix={field.prefix}
                        suffix={field.suffix}
                        multiline={false}
                    />
                );
                
            case 'textarea':
                return (
                    <TextField
                        key={field.key}
                        label={field.label}
                        value={value}
                        onChange={(val) => handleChange(field.key, val)}
                        error={error}
                        placeholder={field.placeholder}
                        disabled={disabled}
                        required={field.required}
                        helpText={field.helpText}
                        multiline={field.rows || 3}
                    />
                );
                
            case 'number':
                return (
                    <TextField
                        key={field.key}
                        label={field.label}
                        type="number"
                        value={String(value)}
                        onChange={(val) => handleChange(field.key, Number(val) || '')}
                        error={error}
                        placeholder={field.placeholder}
                        disabled={disabled}
                        required={field.required}
                        helpText={field.helpText}
                        min={field.min}
                        max={field.max}
                        step={field.step}
                    />
                );
                
            case 'select':
                return (
                    <Select
                        key={field.key}
                        label={field.label}
                        options={field.options || []}
                        value={value}
                        onChange={(val) => handleChange(field.key, val)}
                        error={error}
                        disabled={disabled}
                        required={field.required}
                        helpText={field.helpText}
                    />
                );
                
            case 'checkbox':
            case 'boolean':
                return (
                    <Checkbox
                        key={field.key}
                        label={field.label}
                        checked={Boolean(value)}
                        onChange={(val) => handleChange(field.key, val)}
                        disabled={disabled}
                        helpText={field.helpText}
                    />
                );
                
            case 'products':
                return (
                    <Box key={field.key}>
                        <BlockStack gap="300">
                            <InlineStack align="space-between">
                                <Text variant="bodyMd" fontWeight="medium">
                                    {field.label}
                                    {field.required && <Text tone="critical"> *</Text>}
                                </Text>
                                <Button
                                    onClick={() => setShowProductSelector(true)}
                                    disabled={disabled}
                                >
                                    Select Products
                                </Button>
                            </InlineStack>
                            
                            {error && (
                                <Banner tone="critical">
                                    {error}
                                </Banner>
                            )}
                            
                            {Array.isArray(value) && value.length > 0 && (
                                <BlockStack gap="200">
                                    <Text variant="bodyMd" fontWeight="medium">
                                        Selected Products ({value.length})
                                    </Text>
                                    <InlineStack gap="100" wrap={false}>
                                        {value.map((product) => (
                                            <Badge
                                                key={product.id}
                                                tone="info"
                                                onRemove={disabled ? undefined : () => {
                                                    const updated = value.filter(p => p.id !== product.id);
                                                    handleChange(field.key, updated);
                                                }}
                                            >
                                                {product.title}
                                            </Badge>
                                        ))}
                                    </InlineStack>
                                </BlockStack>
                            )}
                            
                            {field.helpText && (
                                <Text variant="caption" tone="subdued">
                                    {field.helpText}
                                </Text>
                            )}
                        </BlockStack>
                    </Box>
                );
                
            case 'collections':
                return (
                    <Box key={field.key}>
                        <BlockStack gap="300">
                            <InlineStack align="space-between">
                                <Text variant="bodyMd" fontWeight="medium">
                                    {field.label}
                                    {field.required && <Text tone="critical"> *</Text>}
                                </Text>
                                <Button
                                    onClick={() => setShowCollectionSelector(true)}
                                    disabled={disabled}
                                >
                                    Select Collections
                                </Button>
                            </InlineStack>
                            
                            {error && (
                                <Banner tone="critical">
                                    {error}
                                </Banner>
                            )}
                            
                            {Array.isArray(value) && value.length > 0 && (
                                <BlockStack gap="200">
                                    <Text variant="bodyMd" fontWeight="medium">
                                        Selected Collections ({value.length})
                                    </Text>
                                    <InlineStack gap="100" wrap={false}>
                                        {value.map((collection) => (
                                            <Badge
                                                key={collection.id}
                                                tone="info"
                                                onRemove={disabled ? undefined : () => {
                                                    const updated = value.filter(c => c.id !== collection.id);
                                                    handleChange(field.key, updated);
                                                }}
                                            >
                                                {collection.title}
                                            </Badge>
                                        ))}
                                    </InlineStack>
                                </BlockStack>
                            )}
                            
                            {field.helpText && (
                                <Text variant="caption" tone="subdued">
                                    {field.helpText}
                                </Text>
                            )}
                        </BlockStack>
                    </Box>
                );
                
            default:
                return (
                    <TextField
                        key={field.key}
                        label={field.label}
                        value={value}
                        onChange={(val) => handleChange(field.key, val)}
                        error={error}
                        placeholder={field.placeholder}
                        disabled={disabled}
                        required={field.required}
                        helpText={field.helpText}
                    />
                );
        }
    }, [formData, errors, mode, handleChange]);
    
    const title = mode === 'create' ? `Create ${entitySingular}` :
                 mode === 'edit' ? `Edit ${entitySingular}` :
                 `View ${entitySingular}`;
    
    return (
        <>
            <Modal
                open={isOpen}
                onClose={onClose}
                title={title}
                primaryAction={mode !== 'view' ? {
                    content: saving ? 'Saving...' : 'Save',
                    onAction: handleSave,
                    loading: saving,
                    disabled: Object.keys(errors).length > 0
                } : undefined}
                secondaryActions={mode !== 'view' ? [
                    {
                        content: 'Reset',
                        onAction: handleReset,
                        disabled: saving
                    },
                    {
                        content: 'Cancel',
                        onAction: onClose
                    }
                ] : [
                    {
                        content: 'Close',
                        onAction: onClose
                    }
                ]}
                size="medium"
            >
                <Modal.Section>
                    <BlockStack gap="400">
                        {Object.keys(errors).length > 0 && (
                            <Banner tone="critical">
                                Please fix the errors below before saving.
                            </Banner>
                        )}
                        
                        <Card>
                            <Box padding="400">
                                <FormLayout>
                                    {formFields.map(renderField)}
                                </FormLayout>
                            </Box>
                        </Card>
                    </BlockStack>
                </Modal.Section>
            </Modal>
            
            {/* Product Selector */}
            {showProductSelector && (
                <ProductSelector
                    isOpen={showProductSelector}
                    onClose={() => setShowProductSelector(false)}
                    onProductsSelected={(products) => {
                        const productsField = formFields.find(f => f.type === 'products');
                        if (productsField) {
                            handleChange(productsField.key, products);
                        }
                        setShowProductSelector(false);
                    }}
                    selectedProducts={formData[formFields.find(f => f.type === 'products')?.key] || []}
                    multiSelect={true}
                />
            )}
            
            {/* Collection Selector */}
            {showCollectionSelector && (
                <CollectionSelector
                    isOpen={showCollectionSelector}
                    onClose={() => setShowCollectionSelector(false)}
                    onCollectionsSelected={(collections) => {
                        const collectionsField = formFields.find(f => f.type === 'collections');
                        if (collectionsField) {
                            handleChange(collectionsField.key, collections);
                        }
                        setShowCollectionSelector(false);
                    }}
                    selectedCollections={formData[formFields.find(f => f.type === 'collections')?.key] || []}
                    multiSelect={true}
                />
            )}
        </>
    );
}