import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { AppProvider } from "@shopify/polaris";
import translations from "@shopify/polaris/locales/en.json";
import { InertiaProgress } from '@inertiajs/progress';
import "@shopify/polaris/build/esm/styles.css";

InertiaProgress.init();
const appName = import.meta.env.VITE_APP_NAME || "Laravel";
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
                <App {...props} />
            </AppProvider>
        );
    },
    progress: {
        color: "#4B5563",
    },
});
