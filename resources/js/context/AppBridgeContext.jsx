// resources/js/context/AppBridgeContext.jsx
import React, { createContext, useContext } from "react";
import createApp from "@shopify/app-bridge";

// Create Context
const AppBridgeContext = createContext(null);

// Provider Component
export const AppBridgeProvider = ({ children }) => {
    // Ensure host param is always set
    const hostParam = new URLSearchParams(window.location.search).get("host");
    if (hostParam) {
        window.__SHOPIFY_HOST = hostParam;
    }

    const appBridgeConfig = {
        host: window.__SHOPIFY_HOST,
        apiKey: import.meta.env.VITE_SHOPIFY_API_KEY,
        forceRedirect: true,
    };

    // Create Shopify App Bridge instance
    const appBridgeInstance = createApp(appBridgeConfig);

    return (
        <AppBridgeContext.Provider value={appBridgeInstance}>
            {children}
        </AppBridgeContext.Provider>
    );
};

// Hook to access App Bridge instance
export const useAppBridge = () => {
    const context = useContext(AppBridgeContext);
    if (!context) {
        throw new Error(
            "useAppBridge must be used within an AppBridgeProvider"
        );
    }
    return context;
};
