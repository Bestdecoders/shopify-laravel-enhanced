import { useState, useCallback, useMemo } from "react";
import {
    Page,
    Card,
    BlockStack,
    InlineGrid,
    Frame,
    Toast,
    useIndexResourceState,
    EmptyState,
    Spinner,
    Box,
    Text,
    ButtonGroup,
    Button,
    Modal,
    Banner
} from "@shopify/polaris";
import { PlusIcon, ImportIcon, ExportIcon, EditIcon } from "@shopify/polaris-icons";

import { useDataManager } from "../hooks/useDataManager";
import { useDataFilters } from "../hooks/useDataFilters";
import { useImportExport } from "../hooks/useImportExport";
import { useToast } from "../hooks/useToast";
import DataTable from "../components/DataTable";
import DataFilters from "../components/DataFilters";
import DataForm from "../components/DataForm";

// =============================================================================
// ⚠️  DATA MANAGER - Generalized CRUD interface for any data type
// =============================================================================
// This component provides a complete data management interface with:
// - CRUD operations (Create, Read, Update, Delete)
// - Advanced filtering and search
// - Import/export functionality
// - Bulk operations
// - Responsive design with Polaris components
// =============================================================================

export default function DataManager({ 
    entityType = "items",
    entityLabel = "Items",
    entitySingular = "Item",
    apiEndpoint = "/api/data",
    config = {}
}) {
    const {
        toastActive,
        toastMessage,
        toastError,
        showSuccess,
        showError,
        hideToast
    } = useToast();

    const {
        items,
        loading,
        error,
        isModalOpen,
        modalMode,
        currentItem,
        fetchItems,
        createItem,
        updateItem,
        deleteItems,
        activateItems,
        deactivateItems,
        duplicateItem,
        openModal,
        closeModal
    } = useDataManager(entityType, apiEndpoint);

    const {
        isImporting,
        isExporting,
        fileInputRef,
        handleImport,
        handleFileChange,
        handleExport,
        handleExportSelected,
        triggerImport
    } = useImportExport(
        entityType,
        apiEndpoint,
        (message) => {
            showSuccess(message);
            fetchItems();
        },
        (message) => showError(message)
    );

    // Filter configuration
    const filterConfig = useMemo(() => ({
        searchFields: config.searchFields || ['title', 'name'],
        statusField: config.statusField || 'status',
        sortOptions: config.sortOptions || [
            { label: `${entitySingular} A-Z`, value: 'title_asc' },
            { label: `${entitySingular} Z-A`, value: 'title_desc' },
            { label: 'Created (newest first)', value: 'created_at_desc' },
            { label: 'Created (oldest first)', value: 'created_at_asc' },
            { label: 'Updated (newest first)', value: 'updated_at_desc' },
            { label: 'Updated (oldest first)', value: 'updated_at_asc' },
        ],
        statusOptions: config.statusOptions || [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' },
            { label: 'Draft', value: 'draft' },
        ],
        tabs: config.tabs || [
            { id: 'all', content: `All ${entityLabel}`, filter: () => true },
            { id: 'active', content: 'Active', filter: (item) => item.status === 'active' },
            { id: 'inactive', content: 'Inactive', filter: (item) => item.status === 'inactive' },
        ]
    }), [config, entityLabel, entitySingular]);

    const {
        filteredData,
        queryValue,
        sortSelected,
        statusFilter,
        selectedTab,
        taggedWith,
        sortOptions,
        statusOptions,
        tabOptions,
        setQueryValue,
        setSortSelected,
        handleStatusChange,
        setSelectedTab,
        setTaggedWith,
        handleQueryValueRemove,
        handleStatusRemove,
        handleTaggedWithRemove,
        handleFiltersClearAll,
        isEmpty,
        disambiguateLabel,
    } = useDataFilters(items, filterConfig);

    const {
        selectedResources,
        allResourcesSelected,
        handleSelectionChange,
    } = useIndexResourceState(filteredData);

    // CRUD operations
    const handleCreate = useCallback(() => {
        openModal('create');
    }, [openModal]);

    const handleEdit = useCallback((item) => {
        openModal('edit', item);
    }, [openModal]);

    const handleView = useCallback((item) => {
        openModal('view', item);
    }, [openModal]);

    const handleDuplicate = useCallback(async (item) => {
        const result = await duplicateItem(item.id);
        if (result.success) {
            showSuccess(`${entitySingular} duplicated successfully`);
        } else {
            showError(result.error || `Failed to duplicate ${entitySingular.toLowerCase()}`);
        }
    }, [duplicateItem, showSuccess, showError, entitySingular]);

    const handleSave = useCallback(async (formData) => {
        try {
            let result;
            if (modalMode === 'create') {
                result = await createItem(formData);
            } else {
                result = await updateItem(currentItem.id, formData);
            }

            if (result.success) {
                closeModal();
                showSuccess(`${entitySingular} ${modalMode === 'create' ? 'created' : 'updated'} successfully`);
            } else {
                showError(result.error || `Failed to ${modalMode} ${entitySingular.toLowerCase()}`);
            }
        } catch (error) {
            showError(error.message || `Failed to ${modalMode} ${entitySingular.toLowerCase()}`);
        }
    }, [modalMode, currentItem, createItem, updateItem, closeModal, showSuccess, showError, entitySingular]);

    // Bulk operations
    const handleActivate = useCallback(async () => {
        const result = await activateItems(selectedResources, showSuccess, showError);
        if (result.success) {
            showSuccess(`Successfully activated ${selectedResources.length} ${entityType}`);
        }
    }, [selectedResources, activateItems, showSuccess, showError, entityType]);

    const handleDeactivate = useCallback(async () => {
        const result = await deactivateItems(selectedResources, showSuccess, showError);
        if (result.success) {
            showSuccess(`Successfully deactivated ${selectedResources.length} ${entityType}`);
        }
    }, [selectedResources, deactivateItems, showSuccess, showError, entityType]);

    const handleDelete = useCallback(async () => {
        const result = await deleteItems(selectedResources, showSuccess, showError);
        if (result.success) {
            showSuccess(`Successfully deleted ${selectedResources.length} ${entityType}`);
        }
    }, [selectedResources, deleteItems, showSuccess, showError, entityType]);

    const handleBulkExport = useCallback(() => {
        handleExportSelected(selectedResources);
    }, [selectedResources, handleExportSelected]);

    // Bulk actions for resource list
    const promotedBulkActions = [
        {
            content: `Activate ${entityLabel}`,
            onAction: handleActivate,
        },
        {
            content: `Deactivate ${entityLabel}`,
            onAction: handleDeactivate,
        },
    ];

    const bulkActions = [
        {
            content: `Export Selected`,
            onAction: handleBulkExport,
        },
        {
            content: "Delete",
            onAction: handleDelete,
            destructive: true,
        },
    ];

    const toastMarkup = toastActive ? (
        <Toast
            content={toastMessage}
            error={toastError}
            onDismiss={hideToast}
            duration={4000}
        />
    ) : null;

    // Loading state
    if (loading && items.length === 0) {
        return (
            <Page title={entityLabel} fullWidth>
                <Frame>
                    {toastMarkup}
                    <Card sectioned>
                        <Box padding="600" textAlign="center">
                            <Spinner size="large" />
                            <Box paddingBlockStart="300">
                                <Text variant="bodyMd" tone="subdued">
                                    Loading {entityType}...
                                </Text>
                            </Box>
                        </Box>
                    </Card>
                </Frame>
            </Page>
        );
    }

    // Error state
    if (error && items.length === 0) {
        return (
            <Page title={entityLabel} fullWidth>
                <Frame>
                    {toastMarkup}
                    <Banner tone="critical">
                        <p>Failed to load {entityType}. Please refresh the page and try again.</p>
                        <p>Error: {error}</p>
                    </Banner>
                </Frame>
            </Page>
        );
    }

    return (
        <Frame>
            {toastMarkup}
            
            <Page
                title={entityLabel}
                fullWidth
                backAction={{
                    content: 'Back to Dashboard',
                    url: '/home'
                }}
                primaryAction={{
                    content: `Create ${entitySingular}`,
                    onAction: handleCreate,
                    icon: PlusIcon
                }}
                secondaryActions={[
                    {
                        content: 'Import',
                        onAction: triggerImport,
                        icon: ImportIcon,
                        loading: isImporting
                    },
                    {
                        content: 'Export All',
                        onAction: () => handleExport(),
                        icon: ExportIcon,
                        loading: isExporting
                    }
                ]}
            >
                <BlockStack gap="400">
                    {/* Actions Bar */}
                    <Card>
                        <Box padding="400">
                            <InlineGrid columns={{ xs: 1, md: 'auto 1fr auto' }} gap="300">
                                <ButtonGroup>
                                    <Button
                                        onClick={handleCreate}
                                        icon={PlusIcon}
                                        primary
                                    >
                                        Create {entitySingular}
                                    </Button>
                                </ButtonGroup>
                                
                                <Box />
                                
                                <ButtonGroup>
                                    <Button
                                        onClick={triggerImport}
                                        icon={ImportIcon}
                                        loading={isImporting}
                                        disabled={isExporting}
                                    >
                                        Import
                                    </Button>
                                    <Button
                                        onClick={() => handleExport()}
                                        icon={ExportIcon}
                                        loading={isExporting}
                                        disabled={isImporting}
                                    >
                                        Export
                                    </Button>
                                </ButtonGroup>
                            </InlineGrid>
                        </Box>
                    </Card>

                    {/* Filters */}
                    <DataFilters
                        queryValue={queryValue}
                        setQueryValue={setQueryValue}
                        sortSelected={sortSelected}
                        setSortSelected={setSortSelected}
                        statusFilter={statusFilter}
                        handleStatusChange={handleStatusChange}
                        selectedTab={selectedTab}
                        setSelectedTab={setSelectedTab}
                        taggedWith={taggedWith}
                        setTaggedWith={setTaggedWith}
                        tabOptions={tabOptions}
                        sortOptions={sortOptions}
                        statusOptions={statusOptions}
                        handleQueryValueRemove={handleQueryValueRemove}
                        handleStatusRemove={handleStatusRemove}
                        handleTaggedWithRemove={handleTaggedWithRemove}
                        handleFiltersClearAll={handleFiltersClearAll}
                        isEmpty={isEmpty}
                        disambiguateLabel={disambiguateLabel}
                        entityType={entityType}
                    />

                    {/* Data Table */}
                    {filteredData.length === 0 && !loading ? (
                        <Card sectioned>
                            <EmptyState
                                heading={`No ${entityType} found`}
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                                action={{
                                    content: `Create ${entitySingular}`,
                                    onAction: handleCreate
                                }}
                            >
                                <p>
                                    {isEmpty 
                                        ? `Get started by creating your first ${entitySingular.toLowerCase()}.`
                                        : `Try adjusting your search or filters to find what you're looking for.`
                                    }
                                </p>
                            </EmptyState>
                        </Card>
                    ) : (
                        <DataTable
                            items={filteredData}
                            selectedResources={selectedResources}
                            allResourcesSelected={allResourcesSelected}
                            handleSelectionChange={handleSelectionChange}
                            promotedBulkActions={promotedBulkActions}
                            bulkActions={bulkActions}
                            onEdit={handleEdit}
                            onView={handleView}
                            onDuplicate={handleDuplicate}
                            entityType={entityType}
                            entitySingular={entitySingular}
                            loading={loading}
                            config={config}
                        />
                    )}
                </BlockStack>
                
                {/* Hidden file input for import */}
                <input
                    ref={fileInputRef}
                    type="file"
                    accept=".csv,.json,.xlsx,.xls"
                    onChange={handleFileChange}
                    style={{ display: 'none' }}
                />
                
                {/* CRUD Modal */}
                <DataForm
                    isOpen={isModalOpen}
                    mode={modalMode}
                    item={currentItem}
                    onSave={handleSave}
                    onClose={closeModal}
                    entityType={entityType}
                    entitySingular={entitySingular}
                    config={config}
                />
            </Page>
        </Frame>
    );
}