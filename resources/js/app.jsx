import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { AppProvider } from "@shopify/polaris";
import translations from "@shopify/polaris/locales/en.json";
import { InertiaProgress } from '@inertiajs/progress';
import "@shopify/polaris/build/esm/styles.css";
import createApp from '@shopify/app-bridge';
import { NavigationMenu, AppLink } from '@shopify/app-bridge/actions';
// TawkTo commented out for BFS compliance (3.1.1 - no external embedded content)
// Uncomment if you want to enable the chat widget
// import TawkTo from './components/TawkTo';

InertiaProgress.init();
const appName = import.meta.env.VITE_APP_NAME || "Laravel";
window.global = window;

// Initialize Shopify App
const shopifyApp = createApp({
  apiKey: import.meta.env.VITE_SHOPIFY_API_KEY,
  host: new URL(window.location.href).searchParams.get('host'),
});

// Get current host parameter
const currentHost = new URL(window.location.href).searchParams.get('host');

// Create navigation links
const aboutLink = AppLink.create(shopifyApp, {
    label: "About",
    destination: `/about?host=${currentHost}`,
    destinationType: "app",
});

const settingsLink = AppLink.create(shopifyApp, {
    label: "Settings",
    destination: `/settings?host=${currentHost}`,
    destinationType: "app",
});

// Create navigation menu
NavigationMenu.create(shopifyApp, {
  items: [ settingsLink, aboutLink]
});

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
                {/* TawkTo commented out for BFS compliance (3.1.1 - no external embedded content) */}
                {/* <TawkTo /> */}
                <App {...props} />
            </AppProvider>
        );
    },
    progress: {
        color: "#4B5563",
    },
});
