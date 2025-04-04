import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { AppProvider } from "@shopify/polaris";
import { Provider as AppBridgeProvider } from "@shopify/app-bridge-react";
import translations from "@shopify/polaris/locales/en.json";
import { InertiaProgress } from '@inertiajs/progress';
import "@shopify/polaris/build/esm/styles.css";

InertiaProgress.init();
const appName = import.meta.env.VITE_APP_NAME || "Laravel";
const appBridgeConfig = {
    host: new URLSearchParams(location.search).get("host") || window.__SHOPIFY_HOST,
    apiKey: import.meta.env.VITE_SHOPIFY_API_KEY,
    forceRedirect: true,
};

window.__SHOPIFY_HOST = appBridgeConfig.host;
window.global = window;
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob("./Pages/**/*.jsx")
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <AppProvider i18n={translations}>
                <AppBridgeProvider config={appBridgeConfig}>
                    <App {...props} />
                </AppBridgeProvider>
            </AppProvider>
        );
    },
    progress: {
        color: "#4B5563",
    },
});
