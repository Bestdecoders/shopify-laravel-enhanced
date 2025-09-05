import { useState, useCallback } from 'react';

/**
 * Hook for managing toast notifications
 * Provides success, error, warning, and info toast types
 */
export function useToast() {
    const [toastActive, setToastActive] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [toastError, setToastError] = useState(false);
    const [toastDuration, setToastDuration] = useState(4000);
    const [toastAction, setToastAction] = useState(null);
    
    // Show success toast
    const showSuccess = useCallback((message, duration = 4000, action = null) => {
        setToastMessage(message);
        setToastError(false);
        setToastDuration(duration);
        setToastAction(action);
        setToastActive(true);
    }, []);
    
    // Show error toast
    const showError = useCallback((message, duration = 6000, action = null) => {
        setToastMessage(message);
        setToastError(true);
        setToastDuration(duration);
        setToastAction(action);
        setToastActive(true);
    }, []);
    
    // Show warning toast (using error styling with different duration)
    const showWarning = useCallback((message, duration = 5000, action = null) => {
        setToastMessage(message);
        setToastError(true);
        setToastDuration(duration);
        setToastAction(action);
        setToastActive(true);
    }, []);
    
    // Show info toast
    const showInfo = useCallback((message, duration = 4000, action = null) => {
        setToastMessage(message);
        setToastError(false);
        setToastDuration(duration);
        setToastAction(action);
        setToastActive(true);
    }, []);
    
    // Hide toast
    const hideToast = useCallback(() => {
        setToastActive(false);
        setToastMessage('');
        setToastError(false);
        setToastAction(null);
    }, []);
    
    // Show toast with custom options
    const showToast = useCallback((options = {}) => {
        const {
            message = '',
            error = false,
            duration = 4000,
            action = null,
        } = options;
        
        setToastMessage(message);
        setToastError(error);
        setToastDuration(duration);
        setToastAction(action);
        setToastActive(true);
    }, []);
    
    return {
        // State
        toastActive,
        toastMessage,
        toastError,
        toastDuration,
        toastAction,
        
        // Functions
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showToast,
        hideToast,
    };
}