import { useState, useCallback } from "react";
import {
    Card,
    Filters,
    TextField,
    Select,
    Tabs,
    Box,
    InlineStack,
    Text,
    Badge,
    Button,
    Collapsible
} from "@shopify/polaris";
import { SearchIcon, FilterIcon } from "@shopify/polaris-icons";

// =============================================================================
// ⚠️  DATA FILTERS - Generalized filtering interface for data lists
// =============================================================================
// This component provides comprehensive filtering capabilities including:
// - Search across multiple fields
// - Status filtering with multiple selection
// - Sorting options
// - Tabbed views
// - Tag filtering
// - Quick filter clearing
// =============================================================================

export default function DataFilters({
    queryValue = "",
    setQueryValue,
    sortSelected = "",
    setSortSelected,
    statusFilter = [],
    handleStatusChange,
    selectedTab = 0,
    setSelectedTab,
    taggedWith = "",
    setTaggedWith,
    tabOptions = [],
    sortOptions = [],
    statusOptions = [],
    handleQueryValueRemove,
    handleStatusRemove,
    handleTaggedWithRemove,
    handleFiltersClearAll,
    isEmpty = true,
    disambiguateLabel,
    entityType = "items"
}) {
    const [isFiltersOpen, setIsFiltersOpen] = useState(false);
    
    const toggleFilters = useCallback(() => {
        setIsFiltersOpen(prev => !prev);
    }, []);
    
    // Applied filters for chips
    const appliedFilters = [];
    
    if (queryValue) {
        appliedFilters.push({
            key: 'query',
            label: disambiguateLabel('query', queryValue),
            onRemove: handleQueryValueRemove,
        });
    }
    
    if (statusFilter.length > 0) {
        statusFilter.forEach(status => {
            appliedFilters.push({
                key: `status-${status}`,
                label: disambiguateLabel('status', status),
                onRemove: () => {
                    const newStatusFilter = statusFilter.filter(s => s !== status);
                    handleStatusChange(newStatusFilter);
                },
            });
        });
    }
    
    if (taggedWith) {
        appliedFilters.push({
            key: 'taggedWith',
            label: disambiguateLabel('taggedWith', taggedWith),
            onRemove: handleTaggedWithRemove,
        });
    }
    
    // Filter components
    const filters = [
        {
            key: 'status',
            label: 'Status',
            filter: (
                <Select
                    label="Status"
                    placeholder="All statuses"
                    options={[
                        { label: 'All statuses', value: '' },
                        ...statusOptions
                    ]}
                    value={statusFilter.length === 1 ? statusFilter[0] : ''}
                    onChange={(value) => handleStatusChange(value ? [value] : [])}
                />
            ),
            shortcut: true,
        },
        {
            key: 'taggedWith',
            label: 'Tagged with',
            filter: (
                <TextField
                    label="Tagged with"
                    value={taggedWith}
                    onChange={setTaggedWith}
                    placeholder="Enter tags"
                    clearButton
                    onClearButtonClick={handleTaggedWithRemove}
                />
            ),
        },
    ];
    
    const filterCount = appliedFilters.length;
    
    return (
        <Card>
            <Box padding="400">
                {/* Tabs */}
                {tabOptions.length > 0 && (
                    <Box paddingBlockEnd="400">
                        <Tabs
                            tabs={tabOptions}
                            selected={selectedTab}
                            onSelect={setSelectedTab}
                        />
                    </Box>
                )}
                
                {/* Search and Sort */}
                <InlineStack gap="400" align="space-between" blockAlign="start">
                    <Box minWidth="300px" width="100%">
                        <TextField
                            label="Search"
                            value={queryValue}
                            onChange={setQueryValue}
                            placeholder={`Search ${entityType}...`}
                            prefix={<SearchIcon />}
                            clearButton
                            onClearButtonClick={handleQueryValueRemove}
                            labelHidden
                        />
                    </Box>
                    
                    <InlineStack gap="200" align="end">
                        {sortOptions.length > 0 && (
                            <Box minWidth="200px">
                                <Select
                                    label="Sort by"
                                    options={sortOptions}
                                    value={sortSelected}
                                    onChange={setSortSelected}
                                    labelHidden
                                />
                            </Box>
                        )}
                        
                        <Button
                            icon={FilterIcon}
                            onClick={toggleFilters}
                            pressed={isFiltersOpen}
                        >
                            Filters
                            {filterCount > 0 && (
                                <Badge tone="info" size="small">
                                    {filterCount}
                                </Badge>
                            )}
                        </Button>
                    </InlineStack>
                </InlineStack>
                
                {/* Advanced Filters */}
                <Collapsible open={isFiltersOpen}>
                    <Box paddingBlockStart="400">
                        <Filters
                            queryValue={queryValue}
                            filters={filters}
                            appliedFilters={appliedFilters}
                            onQueryChange={setQueryValue}
                            onQueryClear={handleQueryValueRemove}
                            onClearAll={handleFiltersClearAll}
                        />
                    </Box>
                </Collapsible>
                
                {/* Applied Filters Summary */}
                {!isEmpty && (
                    <Box paddingBlockStart="300">
                        <InlineStack gap="200" align="space-between" blockAlign="center">
                            <InlineStack gap="200">
                                <Text variant="bodySm" tone="subdued">
                                    {appliedFilters.length} filter{appliedFilters.length === 1 ? '' : 's'} applied
                                </Text>
                                {appliedFilters.length > 0 && (
                                    <Button
                                        size="slim"
                                        variant="tertiary"
                                        onClick={handleFiltersClearAll}
                                    >
                                        Clear all
                                    </Button>
                                )}
                            </InlineStack>
                            
                            {sortSelected && (
                                <Text variant="caption" tone="subdued">
                                    Sorted by: {sortOptions.find(opt => opt.value === sortSelected)?.label}
                                </Text>
                            )}
                        </InlineStack>
                    </Box>
                )}
            </Box>
        </Card>
    );
}