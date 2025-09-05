import { useState, useMemo, useCallback } from 'react';

/**
 * Generalized hook for filtering and searching data
 * Provides search, sorting, status filtering, and tabbed views
 */
export function useDataFilters(
    data = [], 
    config = {
        searchFields: ['title', 'name'],
        statusField: 'status',
        sortOptions: [
            { label: 'Name A-Z', value: 'name_asc' },
            { label: 'Name Z-A', value: 'name_desc' },
            { label: 'Created (newest first)', value: 'created_desc' },
            { label: 'Created (oldest first)', value: 'created_asc' },
            { label: 'Updated (newest first)', value: 'updated_desc' },
            { label: 'Updated (oldest first)', value: 'updated_asc' },
        ],
        statusOptions: [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' },
            { label: 'Draft', value: 'draft' },
        ],
        tabs: [
            { id: 'all', content: 'All', filter: () => true },
            { id: 'active', content: 'Active', filter: (item) => item.status === 'active' },
            { id: 'inactive', content: 'Inactive', filter: (item) => item.status === 'inactive' },
        ]
    }
) {
    // Filter states
    const [queryValue, setQueryValue] = useState('');
    const [sortSelected, setSortSelected] = useState(config.sortOptions[0]?.value || '');
    const [statusFilter, setStatusFilter] = useState([]);
    const [selectedTab, setSelectedTab] = useState(0);
    const [taggedWith, setTaggedWith] = useState('');
    
    // Search function
    const searchItems = useCallback((items, query) => {
        if (!query) return items;
        
        const lowerQuery = query.toLowerCase();
        return items.filter(item => {
            return config.searchFields.some(field => {
                const value = getNestedValue(item, field);
                return value && value.toString().toLowerCase().includes(lowerQuery);
            });
        });
    }, [config.searchFields]);
    
    // Sort function
    const sortItems = useCallback((items, sortKey) => {
        if (!sortKey) return items;
        
        return [...items].sort((a, b) => {
            const [field, direction] = sortKey.split('_');
            
            let valueA = getNestedValue(a, field) || '';
            let valueB = getNestedValue(b, field) || '';
            
            // Handle dates
            if (field.includes('created') || field.includes('updated')) {
                valueA = new Date(valueA);
                valueB = new Date(valueB);
            }
            
            // Handle strings
            if (typeof valueA === 'string') {
                valueA = valueA.toLowerCase();
                valueB = valueB.toLowerCase();
            }
            
            let comparison = 0;
            if (valueA > valueB) comparison = 1;
            if (valueA < valueB) comparison = -1;
            
            return direction === 'desc' ? -comparison : comparison;
        });
    }, []);
    
    // Filter by status
    const filterByStatus = useCallback((items, statuses) => {
        if (!statuses.length) return items;
        
        return items.filter(item => {
            const itemStatus = getNestedValue(item, config.statusField);
            return statuses.includes(itemStatus);
        });
    }, [config.statusField]);
    
    // Filter by tags
    const filterByTags = useCallback((items, tag) => {
        if (!tag) return items;
        
        return items.filter(item => {
            const tags = item.tags || [];
            return tags.some(itemTag => 
                itemTag.toLowerCase().includes(tag.toLowerCase())
            );
        });
    }, []);
    
    // Get filtered and sorted data
    const filteredData = useMemo(() => {
        let result = [...data];
        
        // Apply tab filter first
        if (config.tabs && config.tabs[selectedTab]) {
            result = result.filter(config.tabs[selectedTab].filter);
        }
        
        // Apply search
        result = searchItems(result, queryValue);
        
        // Apply status filter
        result = filterByStatus(result, statusFilter);
        
        // Apply tag filter
        result = filterByTags(result, taggedWith);
        
        // Apply sorting
        result = sortItems(result, sortSelected);
        
        return result;
    }, [data, queryValue, sortSelected, statusFilter, selectedTab, taggedWith, searchItems, sortItems, filterByStatus, filterByTags, config.tabs]);
    
    // Filter handlers
    const handleQueryValueChange = useCallback((value) => {
        setQueryValue(value);
    }, []);
    
    const handleSortChange = useCallback((value) => {
        setSortSelected(value);
    }, []);
    
    const handleStatusChange = useCallback((value) => {
        setStatusFilter(value);
    }, []);
    
    const handleTabChange = useCallback((tabIndex) => {
        setSelectedTab(tabIndex);
    }, []);
    
    const handleTaggedWithChange = useCallback((value) => {
        setTaggedWith(value);
    }, []);
    
    // Clear functions
    const handleQueryValueRemove = useCallback(() => {
        setQueryValue('');
    }, []);
    
    const handleStatusRemove = useCallback(() => {
        setStatusFilter([]);
    }, []);
    
    const handleTaggedWithRemove = useCallback(() => {
        setTaggedWith('');
    }, []);
    
    const handleFiltersClearAll = useCallback(() => {
        setQueryValue('');
        setStatusFilter([]);
        setTaggedWith('');
        setSortSelected(config.sortOptions[0]?.value || '');
    }, [config.sortOptions]);
    
    // Check if filters are empty
    const isEmpty = useMemo(() => {
        return !queryValue && !statusFilter.length && !taggedWith;
    }, [queryValue, statusFilter, taggedWith]);
    
    // Create tab options for Polaris Tabs component
    const tabOptions = useMemo(() => {
        if (!config.tabs) return [];
        
        return config.tabs.map((tab, index) => {
            const count = data.filter(tab.filter).length;
            return {
                id: tab.id,
                content: count > 0 ? `${tab.content} (${count})` : tab.content,
                badge: count > 0 ? count.toString() : undefined,
            };
        });
    }, [data, config.tabs]);
    
    // Sort options formatted for Select component
    const sortOptions = useMemo(() => {
        return config.sortOptions.map(option => ({
            label: option.label,
            value: option.value,
        }));
    }, [config.sortOptions]);
    
    // Status options for ChoiceList
    const statusOptions = useMemo(() => {
        return config.statusOptions.map(option => ({
            label: option.label,
            value: option.value,
        }));
    }, [config.statusOptions]);
    
    // Disambiguate label for filters
    const disambiguateLabel = useCallback((key, value) => {
        switch (key) {
            case 'taggedWith':
                return `Tagged with "${value}"`;
            case 'status':
                const statusOption = config.statusOptions.find(option => option.value === value);
                return statusOption ? statusOption.label : value;
            default:
                return value;
        }
    }, [config.statusOptions]);
    
    return {
        // Filtered data
        filteredData,
        
        // Filter states
        queryValue,
        sortSelected,
        statusFilter,
        selectedTab,
        taggedWith,
        
        // Options
        sortOptions,
        statusOptions,
        tabOptions,
        
        // Handlers
        setQueryValue: handleQueryValueChange,
        setSortSelected: handleSortChange,
        handleStatusChange,
        setSelectedTab: handleTabChange,
        setTaggedWith: handleTaggedWithChange,
        
        // Clear handlers
        handleQueryValueRemove,
        handleStatusRemove,
        handleTaggedWithRemove,
        handleFiltersClearAll,
        
        // Utilities
        isEmpty,
        disambiguateLabel,
    };
}

// Helper function to get nested object values
function getNestedValue(obj, path) {
    return path.split('.').reduce((current, key) => {
        return current && current[key] !== undefined ? current[key] : null;
    }, obj);
}