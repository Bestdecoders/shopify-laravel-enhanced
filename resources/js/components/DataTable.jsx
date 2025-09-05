import {
    ResourceList,
    ResourceItem,
    Avatar,
    Text,
    Box,
    BlockStack,
    InlineStack,
    Badge,
    ButtonGroup,
    Button,
    Card,
    Spinner
} from "@shopify/polaris";
import { ViewIcon, EditIcon, DuplicateIcon, DeleteIcon } from "@shopify/polaris-icons";

// =============================================================================
// ⚠️  DATA TABLE - Generalized resource list for displaying data
// =============================================================================
// This component provides a flexible table interface that can display any
// type of data with consistent formatting and actions
// =============================================================================

export default function DataTable({
    items = [],
    selectedResources = [],
    allResourcesSelected = false,
    handleSelectionChange,
    promotedBulkActions = [],
    bulkActions = [],
    onEdit,
    onView,
    onDuplicate,
    onDelete,
    entityType = "items",
    entitySingular = "Item",
    loading = false,
    config = {}
}) {
    
    // Get display fields from config
    const displayFields = config.displayFields || [
        { key: 'title', label: 'Title' },
        { key: 'status', label: 'Status' },
        { key: 'created_at', label: 'Created' }
    ];
    
    // Format field value for display
    const formatValue = (item, field) => {
        const value = getNestedValue(item, field.key);
        
        if (!value) return '-';
        
        switch (field.type) {
            case 'date':
                return new Date(value).toLocaleDateString();
            case 'datetime':
                return new Date(value).toLocaleString();
            case 'status':
                return (
                    <Badge 
                        tone={getStatusTone(value)}
                        size="small"
                    >
                        {value.charAt(0).toUpperCase() + value.slice(1)}
                    </Badge>
                );
            case 'boolean':
                return value ? 'Yes' : 'No';
            case 'number':
                return typeof value === 'number' ? value.toLocaleString() : value;
            default:
                return String(value);
        }
    };
    
    // Get status badge tone
    const getStatusTone = (status) => {
        switch (status?.toLowerCase()) {
            case 'active':
            case 'published':
            case 'enabled':
                return 'success';
            case 'inactive':
            case 'disabled':
                return 'critical';
            case 'draft':
            case 'pending':
                return 'attention';
            default:
                return 'info';
        }
    };
    
    // Get item image/avatar
    const getItemMedia = (item) => {
        const imageField = config.imageField || 'image';
        const image = getNestedValue(item, imageField);
        
        if (image) {
            const imageUrl = typeof image === 'string' ? image : image.url || image.src;
            if (imageUrl) {
                return (
                    <Avatar
                        source={imageUrl}
                        size="medium"
                        alt={item.title || item.name || `${entitySingular} image`}
                    />
                );
            }
        }
        
        return (
            <Avatar
                source=""
                size="medium"
                initials={getInitials(item.title || item.name || entitySingular)}
            />
        );
    };
    
    // Get initials for avatar
    const getInitials = (text) => {
        return text
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .substring(0, 2)
            .toUpperCase();
    };
    
    // Render individual item
    const renderItem = (item) => {
        const itemId = item.id || item._id;
        const primaryField = displayFields.find(field => field.primary) || displayFields[0];
        const secondaryFields = displayFields.filter(field => !field.primary).slice(0, 2);
        
        return (
            <ResourceItem
                id={itemId}
                media={getItemMedia(item)}
                accessibilityLabel={`${entitySingular}: ${getNestedValue(item, primaryField.key)}`}
                shortcutActions={[
                    {
                        content: 'View',
                        icon: ViewIcon,
                        onAction: () => onView && onView(item),
                    },
                    {
                        content: 'Edit',
                        icon: EditIcon,
                        onAction: () => onEdit && onEdit(item),
                    },
                    {
                        content: 'Duplicate',
                        icon: DuplicateIcon,
                        onAction: () => onDuplicate && onDuplicate(item),
                    },
                ]}
            >
                <Box>
                    <InlineStack align="space-between" blockAlign="start">
                        <BlockStack gap="100">
                            {/* Primary field (title/name) */}
                            <Text variant="bodyMd" fontWeight="semibold">
                                {formatValue(item, primaryField)}
                            </Text>
                            
                            {/* Secondary fields */}
                            {secondaryFields.map((field, index) => (
                                <InlineStack key={field.key} gap="200" align="start">
                                    <Text variant="caption" tone="subdued">
                                        {field.label}:
                                    </Text>
                                    <Text variant="caption">
                                        {formatValue(item, field)}
                                    </Text>
                                </InlineStack>
                            ))}
                            
                            {/* Additional metadata */}
                            {item.updated_at && (
                                <Text variant="caption" tone="subdued">
                                    Updated {new Date(item.updated_at).toLocaleDateString()}
                                </Text>
                            )}
                        </BlockStack>
                        
                        {/* Actions */}
                        <ButtonGroup variant="segmented">
                            {onView && (
                                <Button
                                    icon={ViewIcon}
                                    size="slim"
                                    onClick={() => onView(item)}
                                    accessibilityLabel={`View ${entitySingular.toLowerCase()}`}
                                />
                            )}
                            {onEdit && (
                                <Button
                                    icon={EditIcon}
                                    size="slim"
                                    onClick={() => onEdit(item)}
                                    accessibilityLabel={`Edit ${entitySingular.toLowerCase()}`}
                                />
                            )}
                            {onDuplicate && (
                                <Button
                                    icon={DuplicateIcon}
                                    size="slim"
                                    onClick={() => onDuplicate(item)}
                                    accessibilityLabel={`Duplicate ${entitySingular.toLowerCase()}`}
                                />
                            )}
                        </ButtonGroup>
                    </InlineStack>
                </Box>
            </ResourceItem>
        );
    };
    
    return (
        <Card>
            {loading && (
                <Box padding="400" textAlign="center">
                    <InlineStack align="center" gap="200">
                        <Spinner size="small" />
                        <Text variant="bodySm" tone="subdued">
                            Loading {entityType}...
                        </Text>
                    </InlineStack>
                </Box>
            )}
            
            <ResourceList
                resourceName={{
                    singular: entitySingular.toLowerCase(),
                    plural: entityType.toLowerCase(),
                }}
                items={items}
                renderItem={renderItem}
                selectedItems={selectedResources}
                onSelectionChange={handleSelectionChange}
                promotedBulkActions={promotedBulkActions}
                bulkActions={bulkActions}
                loading={loading}
                showHeader
                totalItemsCount={items.length}
                sortValue=""
                sortOptions={[]}
            />
        </Card>
    );
}

// Helper function to get nested object values
function getNestedValue(obj, path) {
    if (!path) return null;
    return path.split('.').reduce((current, key) => {
        return current && current[key] !== undefined ? current[key] : null;
    }, obj);
}