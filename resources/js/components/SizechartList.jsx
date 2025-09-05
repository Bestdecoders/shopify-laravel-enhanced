import React from "react";
import {
    useIndexResourceState,
    Toast,
    Frame,
} from "@shopify/polaris";
import "@shopify/polaris/build/esm/styles.css";

import { useSizeCharts } from "../hooks/useSizeCharts";
import { useImportExport } from "../hooks/useImportExport";
import { useToast } from "../hooks/useToast";
import { useSizeChartFilters } from "../hooks/useSizeChartFilters";
import SizeChartActions from "./SizeChart/SizeChartActions";
import SizeChartFilters from "./SizeChart/SizeChartFilters";
import SizeChartTable from "./SizeChart/SizeChartTable";

const SizechartList = () => {
    const {
        sizeCharts,
        error,
        fetchSizeCharts,
        activateSizeCharts,
        deactivateSizeCharts,
        deleteSizeCharts
    } = useSizeCharts();

    const {
        toastActive,
        toastMessage,
        toastError,
        showSuccess,
        showError,
        hideToast
    } = useToast();

    const {
        isImporting,
        isExporting,
        fileInputRef,
        handleImport,
        handleFileChange,
        handleExport
    } = useImportExport(
        (message) => {
            showSuccess(message);
            fetchSizeCharts();
        },
        (message) => showError(message)
    );

    // Use filtering hook
    const {
        filteredSizeCharts,
        queryValue,
        setQueryValue,
        sortSelected,
        setSortSelected,
        statusFilter,
        selectedTab,
        setSelectedTab,
        tabOptions,
        handleStatusChange,
        handleStatusRemove,
        handleQueryValueRemove,
        handleFiltersClearAll,
        isEmpty,
        disambiguateLabel
    } = useSizeChartFilters(sizeCharts);

    const onCreateNewView = (value) => {
        showSuccess(`Created new view: ${value}`);
    };


    const {
        selectedResources,
        allResourcesSelected,
        handleSelectionChange,
    } = useIndexResourceState(filteredSizeCharts);

    const handleActivate = async () => {
        const result = await activateSizeCharts(selectedResources, showSuccess, showError);
        if (result?.success) {
            showSuccess(`Successfully activated ${selectedResources.length} size chart(s)`);
        } else {
            showError(result?.error || 'Failed to activate size charts');
        }
    };

    const handleDeactivate = async () => {
        const result = await deactivateSizeCharts(selectedResources, showSuccess, showError);
        if (result?.success) {
            showSuccess(`Successfully deactivated ${selectedResources.length} size chart(s)`);
        } else {
            showError(result?.error || 'Failed to deactivate size charts');
        }
    };

    const handleDelete = async () => {
        const result = await deleteSizeCharts(selectedResources, showSuccess, showError);
        if (result?.success) {
            showSuccess(`Successfully deleted ${selectedResources.length} size chart(s)`);
        } else {
            showError(result?.error || 'Failed to delete size charts');
        }
    };

    const promotedBulkActions = [
        {
            content: "Activate SizeCharts",
            onAction: handleActivate,
        },
        {
            content: "Deactivate SizeChart",
            onAction: handleDeactivate,
        },
        {
            content: "Delete",
            onAction: handleDelete,
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

    if (error) {
        showError(`Error loading size charts: ${error}`);
    }

    return (
        <Frame>
            {toastMarkup}
            
            <SizeChartActions
                isImporting={isImporting}
                isExporting={isExporting}
                fileInputRef={fileInputRef}
                handleImport={handleImport}
                handleFileChange={handleFileChange}
                handleExport={handleExport}
                selectedResources={selectedResources}
            />

            <SizeChartFilters
                queryValue={queryValue}
                setQueryValue={setQueryValue}
                sortSelected={sortSelected}
                setSortSelected={setSortSelected}
                statusFilter={statusFilter}
                selectedTab={selectedTab}
                setSelectedTab={setSelectedTab}
                tabOptions={tabOptions}
                handleStatusChange={handleStatusChange}
                handleStatusRemove={handleStatusRemove}
                handleQueryValueRemove={handleQueryValueRemove}
                handleFiltersClearAll={handleFiltersClearAll}
                isEmpty={isEmpty}
                disambiguateLabel={disambiguateLabel}
                onCreateNewView={onCreateNewView}
            />

            <SizeChartTable
                sizeCharts={filteredSizeCharts}
                selectedResources={selectedResources}
                allResourcesSelected={allResourcesSelected}
                handleSelectionChange={handleSelectionChange}
                promotedBulkActions={promotedBulkActions}
            />
        </Frame>
    );
};

export default SizechartList;