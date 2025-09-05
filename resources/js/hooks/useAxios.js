import axios from "axios";
import { useEffect, useMemo, useCallback } from "react";
import { getSessionToken } from "@shopify/app-bridge-utils";
import { useAppBridge } from "@shopify/app-bridge-react";
import { Redirect } from "@shopify/app-bridge/actions";

export const useAxios = () => {
    const app = useAppBridge();
    const host = useMemo(() => 
        new URLSearchParams(window.location.search).get("host") || 
        window.__SHOPIFY_HOST,
        []
    );

    // Create dedicated axios instance to avoid global pollution
    const axiosInstance = useMemo(() => axios.create({
        timeout: 30000, // 30 second timeout
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    }), []);

    // Enhanced error handler
    const handleRedirect = useCallback((url) => {
        try {
            const redirect = Redirect.create(app);
            redirect.dispatch(Redirect.Action.APP, url);
        } catch (fallbackError) {
            console.warn("App Bridge redirect failed, using window.location", fallbackError);
            window.location.href = url;
        }
    }, [app]);

    useEffect(() => {
        // Request interceptor with enhanced error handling
        const requestInterceptor = axiosInstance.interceptors.request.use(
            async (config) => {
                try {
                    const token = await getSessionToken(app);
                    config.headers.Authorization = `Bearer ${token}`;
                    
                    // Always include host parameter
                    config.params = { 
                        ...(config.params || {}), 
                        host 
                    };

                    // Add CSRF token if available
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                        config.headers['X-CSRF-TOKEN'] = csrfToken;
                    }

                    // Log requests in development
                    if (process.env.NODE_ENV === 'development') {
                        console.log('🚀 API Request:', config.method?.toUpperCase(), config.url);
                    }

                    return config;
                } catch (error) {
                    console.error("❌ Failed to get session token:", error);
                    return Promise.reject(error);
                }
            },
            (error) => {
                console.error("❌ Request interceptor error:", error);
                return Promise.reject(error);
            }
        );

        // Response interceptor with comprehensive error handling
        const responseInterceptor = axiosInstance.interceptors.response.use(
            (response) => {
                // Log successful responses in development
                if (process.env.NODE_ENV === 'development') {
                    console.log('✅ API Response:', response.status, response.config.url);
                }
                return response;
            },
            (error) => {
                const { response, request } = error;

                // Network error (no response received)
                if (!response && request) {
                    console.error("❌ Network error - no response received");
                    return Promise.reject(new Error("Network error. Please check your connection."));
                }

                // Handle specific status codes
                if (response) {
                    const { status, data } = response;
                    
                    switch (status) {
                        case 401:
                            console.error("❌ Unauthorized - redirecting to auth");
                            // Handle unauthorized - could redirect to auth
                            break;
                            
                        case 403:
                            if (data?.forceRedirectUrl) {
                                console.log("🔄 Forcing redirect to:", data.forceRedirectUrl);
                                handleRedirect(data.forceRedirectUrl);
                                return Promise.reject(new Error("Redirecting to billing..."));
                            }
                            break;
                            
                        case 422:
                            console.error("❌ Validation errors:", data?.errors);
                            break;
                            
                        case 429:
                            console.error("❌ Rate limit exceeded");
                            break;
                            
                        case 500:
                            console.error("❌ Server error");
                            break;
                            
                        default:
                            console.error(`❌ HTTP ${status}:`, data?.message || error.message);
                    }
                }

                return Promise.reject(error);
            }
        );

        // Cleanup interceptors on unmount
        return () => {
            axiosInstance.interceptors.request.eject(requestInterceptor);
            axiosInstance.interceptors.response.eject(responseInterceptor);
        };
    }, [app, host, handleRedirect, axiosInstance]);

    return axiosInstance;
};
