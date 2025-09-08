import axios from "axios";
import { useEffect } from "react";
import { useAppBridge } from "@shopify/app-bridge-react";

export const useAxios = () => {
    const shopify = useAppBridge();
    const host =
        new URLSearchParams(window.location.search).get("host") ||
        window.__SHOPIFY_HOST;

    useEffect(() => {
        axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

        const interceptor = axios.interceptors.request.use(async (config) => {
            try {
                const tokenPromise = shopify.idToken();
                const timeoutPromise = new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('Token request timeout')), 5000)
                );
                const token = await Promise.race([tokenPromise, timeoutPromise]);
                
                config.headers.Authorization = `Bearer ${token}`;
                config.params = { ...config.params, host, token }; // Add token parameter for Laravel
                return config;
            } catch (error) {
                console.error("Failed to get session token:", error);
                throw error;
            }
        });

        const responseInterceptor = axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (
                    error.response?.status === 403 &&
                    error.response?.data?.forceRedirectUrl
                ) {
                    window.location.href = error.response.data.forceRedirectUrl;
                }
                return Promise.reject(error);
            }
        );

        return () => {
            axios.interceptors.request.eject(interceptor);
            axios.interceptors.response.eject(responseInterceptor);
        };
    }, [shopify, host]);

    return axios;
};
