import { useState, useEffect, useCallback } from 'react';
import { useAuthenticatedFetch } from './useAuthenticatedFetch';

/**
 * Generalized hook for managing CRUD operations on data entities
 * Can be used for size charts, products, collections, or any other data type
 */
export function useDataManager(entityType = 'items', apiEndpoint = '/api/data') {
    const fetch = useAuthenticatedFetch();
    
    // State management
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [currentItem, setCurrentItem] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit' | 'view'
    
    // Fetch all items
    const fetchItems = useCallback(async (params = {}) => {
        try {
            setLoading(true);
            setError(null);
            
            const queryParams = new URLSearchParams(params).toString();
            const url = queryParams ? `${apiEndpoint}?${queryParams}` : apiEndpoint;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`Failed to fetch ${entityType}`);
            }
            
            const data = await response.json();
            setItems(Array.isArray(data) ? data : data.data || []);
        } catch (err) {
            setError(err.message);
            console.error(`Error fetching ${entityType}:`, err);
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Create a new item
    const createItem = useCallback(async (itemData) => {
        try {
            setLoading(true);
            const response = await fetch(apiEndpoint, {
                method: 'POST',
                body: JSON.stringify(itemData),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to create ${entityType.slice(0, -1)}`);
            }
            
            const newItem = await response.json();
            setItems(prev => [newItem, ...prev]);
            
            return { success: true, data: newItem };
        } catch (err) {
            setError(err.message);
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Update an existing item
    const updateItem = useCallback(async (id, itemData) => {
        try {
            setLoading(true);
            const response = await fetch(`${apiEndpoint}/${id}`, {
                method: 'PUT',
                body: JSON.stringify(itemData),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to update ${entityType.slice(0, -1)}`);
            }
            
            const updatedItem = await response.json();
            setItems(prev => prev.map(item => 
                item.id === id ? updatedItem : item
            ));
            
            return { success: true, data: updatedItem };
        } catch (err) {
            setError(err.message);
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Delete items (single or multiple)
    const deleteItems = useCallback(async (ids, successCallback, errorCallback) => {
        try {
            setLoading(true);
            const idsArray = Array.isArray(ids) ? ids : [ids];
            
            const response = await fetch(`${apiEndpoint}/delete`, {
                method: 'DELETE',
                body: JSON.stringify({ ids: idsArray }),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to delete ${entityType}`);
            }
            
            // Remove deleted items from state
            setItems(prev => prev.filter(item => !idsArray.includes(item.id)));
            
            if (successCallback) {
                successCallback(`Successfully deleted ${idsArray.length} ${entityType}`);
            }
            
            return { success: true, deletedIds: idsArray };
        } catch (err) {
            setError(err.message);
            if (errorCallback) {
                errorCallback(err.message);
            }
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Activate items (useful for toggleable entities)
    const activateItems = useCallback(async (ids, successCallback, errorCallback) => {
        try {
            setLoading(true);
            const idsArray = Array.isArray(ids) ? ids : [ids];
            
            const response = await fetch(`${apiEndpoint}/activate`, {
                method: 'POST',
                body: JSON.stringify({ ids: idsArray }),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to activate ${entityType}`);
            }
            
            // Update items status in state
            setItems(prev => prev.map(item => 
                idsArray.includes(item.id) ? { ...item, status: 'active' } : item
            ));
            
            if (successCallback) {
                successCallback(`Successfully activated ${idsArray.length} ${entityType}`);
            }
            
            return { success: true, activatedIds: idsArray };
        } catch (err) {
            setError(err.message);
            if (errorCallback) {
                errorCallback(err.message);
            }
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Deactivate items
    const deactivateItems = useCallback(async (ids, successCallback, errorCallback) => {
        try {
            setLoading(true);
            const idsArray = Array.isArray(ids) ? ids : [ids];
            
            const response = await fetch(`${apiEndpoint}/deactivate`, {
                method: 'POST',
                body: JSON.stringify({ ids: idsArray }),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to deactivate ${entityType}`);
            }
            
            // Update items status in state
            setItems(prev => prev.map(item => 
                idsArray.includes(item.id) ? { ...item, status: 'inactive' } : item
            ));
            
            if (successCallback) {
                successCallback(`Successfully deactivated ${idsArray.length} ${entityType}`);
            }
            
            return { success: true, deactivatedIds: idsArray };
        } catch (err) {
            setError(err.message);
            if (errorCallback) {
                errorCallback(err.message);
            }
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Duplicate an item
    const duplicateItem = useCallback(async (id) => {
        try {
            setLoading(true);
            const response = await fetch(`${apiEndpoint}/${id}/duplicate`, {
                method: 'POST',
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to duplicate ${entityType.slice(0, -1)}`);
            }
            
            const duplicatedItem = await response.json();
            setItems(prev => [duplicatedItem, ...prev]);
            
            return { success: true, data: duplicatedItem };
        } catch (err) {
            setError(err.message);
            return { success: false, error: err.message };
        } finally {
            setLoading(false);
        }
    }, [fetch, entityType, apiEndpoint]);
    
    // Modal management
    const openModal = useCallback((mode = 'create', item = null) => {
        setModalMode(mode);
        setCurrentItem(item);
        setIsModalOpen(true);
    }, []);
    
    const closeModal = useCallback(() => {
        setIsModalOpen(false);
        setCurrentItem(null);
        setModalMode('create');
    }, []);
    
    // Initialize data on mount
    useEffect(() => {
        fetchItems();
    }, [fetchItems]);
    
    return {
        // Data state
        items,
        loading,
        error,
        
        // Modal state
        isModalOpen,
        modalMode,
        currentItem,
        
        // Data operations
        fetchItems,
        createItem,
        updateItem,
        deleteItems,
        activateItems,
        deactivateItems,
        duplicateItem,
        
        // Modal operations
        openModal,
        closeModal,
        
        // Utility
        setItems,
        setError,
    };
}