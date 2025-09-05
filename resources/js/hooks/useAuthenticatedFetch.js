import { usePage } from '@inertiajs/react';

/**
 * Custom hook for making authenticated API requests
 * Uses Inertia.js props for CSRF token and authentication
 */
export function useAuthenticatedFetch() {
    const { props } = usePage();
    
    return async (url, options = {}) => {
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        };

        const config = {
            headers: {
                ...defaultHeaders,
                ...options.headers,
            },
            credentials: 'same-origin',
            ...options,
        };

        try {
            const response = await fetch(url, config);
            return response;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    };
}