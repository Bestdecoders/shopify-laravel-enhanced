import React, { useEffect } from 'react';

/**
 * TawkTo Chat Widget Component
 * Loads Tawk.to chat widget on all pages
 *
 * Environment variables needed:
 * - VITE_TAWK_TO_PROPERTY_ID
 * - VITE_TAWK_TO_WIDGET_ID
 * - VITE_TAWK_TO_ENABLED (optional, defaults to true)
 */
const TawkTo = () => {
    useEffect(() => {
        const propertyId = import.meta.env.VITE_TAWK_TO_PROPERTY_ID;
        const widgetId = import.meta.env.VITE_TAWK_TO_WIDGET_ID;
        const enabled = import.meta.env.VITE_TAWK_TO_ENABLED !== 'false';

        if (!enabled || !propertyId || !widgetId) {
            return;
        }

        // Check if script is already loaded
        if (window.Tawk_API) {
            return;
        }

        // Initialize Tawk_API
        window.Tawk_API = window.Tawk_API || {};
        window.Tawk_LoadStart = new Date();

        // Create and inject script
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://embed.tawk.to/${propertyId}/${widgetId}`;
        script.charset = 'UTF-8';
        script.setAttribute('crossorigin', '*');

        const firstScript = document.getElementsByTagName('script')[0];
        firstScript.parentNode.insertBefore(script, firstScript);

        // Cleanup function
        return () => {
            // Remove tawk.to iframe and script if component unmounts
            const tawkFrame = document.querySelector('iframe[title*="chat widget"]');
            if (tawkFrame) {
                tawkFrame.remove();
            }
        };
    }, []);

    return null; // This component doesn't render anything
};

export default TawkTo;
