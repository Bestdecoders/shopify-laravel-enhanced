import axios from "axios";
import { useEffect } from "react";
import { getSessionToken } from "@shopify/app-bridge-utils";
import { useAppBridge } from "@shopify/app-bridge-react";

export const useAxios = () => {
    const app = useAppBridge();
    const host =
        new URLSearchParams(window.location.search).get("host") ||
        window.__SHOPIFY_HOST;

    useEffect(() => {
        axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

        const interceptor = axios.interceptors.request.use(async (config) => {
            try {
                const token = await getSessionToken(app);
                config.headers.Authorization = `Bearer ${token}`;
                config.params = { ...config.params, host }; // Automatically include host in the request params
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
    }, [app, host]);

    return axios;
};
