import { useState, useRef, useCallback } from 'react';
import { useAuthenticatedFetch } from './useAuthenticatedFetch';

/**
 * Generalized hook for import/export functionality
 * Supports CSV, JSON, and Excel formats
 */
export function useImportExport(
    entityType = 'data',
    apiEndpoint = '/api/data',
    onSuccess = () => {},
    onError = () => {}
) {
    const fetch = useAuthenticatedFetch();
    const fileInputRef = useRef(null);
    
    // State management
    const [isImporting, setIsImporting] = useState(false);
    const [isExporting, setIsExporting] = useState(false);
    const [importProgress, setImportProgress] = useState(0);
    const [exportProgress, setExportProgress] = useState(0);
    
    // Import data from file
    const handleImport = useCallback(async (file, options = {}) => {
        if (!file) {
            onError('Please select a file to import');
            return { success: false, error: 'No file selected' };
        }
        
        // Validate file type
        const allowedTypes = options.allowedTypes || [
            'text/csv',
            'application/json',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            onError('Invalid file type. Please upload a CSV, JSON, or Excel file.');
            return { success: false, error: 'Invalid file type' };
        }
        
        // Validate file size (default 10MB)
        const maxSize = options.maxSize || 10 * 1024 * 1024;
        if (file.size > maxSize) {
            onError(`File too large. Maximum size is ${Math.round(maxSize / 1024 / 1024)}MB.`);
            return { success: false, error: 'File too large' };
        }
        
        try {
            setIsImporting(true);
            setImportProgress(0);
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('entity_type', entityType);
            
            // Add any additional options
            Object.keys(options).forEach(key => {
                if (key !== 'allowedTypes' && key !== 'maxSize') {
                    formData.append(key, options[key]);
                }
            });
            
            const response = await fetch(`${apiEndpoint}/import`, {
                method: 'POST',
                body: formData,
                headers: {
                    // Don't set Content-Type, let browser set it with boundary for FormData
                },
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to import ${entityType}`);
            }
            
            const result = await response.json();
            setImportProgress(100);
            
            onSuccess(result.message || `Successfully imported ${result.count || 0} ${entityType}`);
            
            return { success: true, data: result };
        } catch (error) {
            onError(error.message || `Failed to import ${entityType}`);
            return { success: false, error: error.message };
        } finally {
            setIsImporting(false);
            setTimeout(() => setImportProgress(0), 2000);
        }
    }, [fetch, entityType, apiEndpoint, onSuccess, onError]);
    
    // Handle file input change
    const handleFileChange = useCallback(async (event) => {
        const file = event.target.files[0];
        if (file) {
            await handleImport(file);
        }
        // Reset the input so the same file can be selected again
        if (event.target) {
            event.target.value = '';
        }
    }, [handleImport]);
    
    // Export data to file
    const handleExport = useCallback(async (options = {}) => {
        try {
            setIsExporting(true);
            setExportProgress(0);
            
            const params = new URLSearchParams({
                entity_type: entityType,
                format: options.format || 'csv',
                ...options.filters || {},
            });
            
            const response = await fetch(`${apiEndpoint}/export?${params.toString()}`, {
                method: 'GET',
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to export ${entityType}`);
            }
            
            setExportProgress(50);
            
            // Get filename from response headers or generate one
            const contentDisposition = response.headers.get('content-disposition');
            let filename = `${entityType}_export_${new Date().toISOString().split('T')[0]}.${options.format || 'csv'}`;
            
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                if (filenameMatch) {
                    filename = filenameMatch[1];
                }
            }
            
            // Download the file
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            setExportProgress(100);
            onSuccess(`Successfully exported ${entityType} to ${filename}`);
            
            return { success: true, filename };
        } catch (error) {
            onError(error.message || `Failed to export ${entityType}`);
            return { success: false, error: error.message };
        } finally {
            setIsExporting(false);
            setTimeout(() => setExportProgress(0), 2000);
        }
    }, [fetch, entityType, apiEndpoint, onSuccess, onError]);
    
    // Export selected items
    const handleExportSelected = useCallback(async (selectedIds, options = {}) => {
        if (!selectedIds || selectedIds.length === 0) {
            onError('No items selected for export');
            return { success: false, error: 'No items selected' };
        }
        
        return handleExport({
            ...options,
            selected_ids: selectedIds.join(','),
        });
    }, [handleExport, onError]);
    
    // Trigger file input
    const triggerImport = useCallback(() => {
        if (fileInputRef.current) {
            fileInputRef.current.click();
        }
    }, []);
    
    // Import from URL
    const handleImportFromUrl = useCallback(async (url, options = {}) => {
        try {
            setIsImporting(true);
            setImportProgress(0);
            
            const response = await fetch(`${apiEndpoint}/import-url`, {
                method: 'POST',
                body: JSON.stringify({
                    url,
                    entity_type: entityType,
                    ...options,
                }),
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to import ${entityType} from URL`);
            }
            
            const result = await response.json();
            setImportProgress(100);
            
            onSuccess(result.message || `Successfully imported ${result.count || 0} ${entityType} from URL`);
            
            return { success: true, data: result };
        } catch (error) {
            onError(error.message || `Failed to import ${entityType} from URL`);
            return { success: false, error: error.message };
        } finally {
            setIsImporting(false);
            setTimeout(() => setImportProgress(0), 2000);
        }
    }, [fetch, entityType, apiEndpoint, onSuccess, onError]);
    
    return {
        // State
        isImporting,
        isExporting,
        importProgress,
        exportProgress,
        
        // Refs
        fileInputRef,
        
        // Import functions
        handleImport,
        handleFileChange,
        handleImportFromUrl,
        triggerImport,
        
        // Export functions
        handleExport,
        handleExportSelected,
    };
}