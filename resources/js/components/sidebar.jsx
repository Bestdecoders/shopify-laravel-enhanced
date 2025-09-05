import React, { useEffect, useState } from "react";
import { Frame, Navigation, Spinner, Box, Text } from "@shopify/polaris";
import {
    HomeIcon,
    ChartVerticalIcon,
    CodeIcon,
    QuestionCircleIcon,
    ChatIcon,
    ThumbsUpIcon,
    NotificationIcon,
    UploadIcon,
    ProductIcon,
    CreditCardIcon,
    SettingsIcon,
    ExternalIcon
} from "@shopify/polaris-icons";
import { getSessionToken } from "@shopify/app-bridge-utils";
import { useAppBridge } from "@shopify/app-bridge-react";
import { router } from "@inertiajs/react";

// =============================================================================
// ⚠️  SIDEBAR CONFIGURATION - Data is loaded from JSON
// =============================================================================
// Sidebar navigation is loaded from resources/data/sidebar-config.json
// To customize for your app, edit the JSON file instead of this component
// =============================================================================

// Icon mapping for JSON configuration
const iconMap = {
    HomeIcon,
    ChartVerticalIcon,
    CodeIcon,
    QuestionCircleIcon,
    ChatIcon,
    ThumbsUpIcon,
    NotificationIcon,
    UploadIcon,
    ProductIcon,
    CreditCardIcon,
    SettingsIcon,
    ExternalIcon
};

const Sidebar = ({ currentPath = "/" }) => {
    const app = useAppBridge();
    const [host, setHost] = useState(null);
    const [token, setToken] = useState(null);
    const [sidebarConfig, setSidebarConfig] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Load sidebar configuration
    useEffect(() => {
        const loadSidebarConfig = async () => {
            try {
                setLoading(true);
                const response = await fetch('/resources/data/sidebar-config.json');
                if (!response.ok) {
                    throw new Error('Failed to load sidebar config');
                }
                const config = await response.json();
                setSidebarConfig(config);
            } catch (err) {
                console.error('Error loading sidebar config:', err);
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadSidebarConfig();
    }, []);

    // Fetch and set host and token
    useEffect(() => {
        const currentHost =
            new URLSearchParams(window.location.search).get("host") ||
            window.__SHOPIFY_HOST;

        setHost(currentHost);

        const fetchToken = async () => {
            try {
                const sessionToken = await getSessionToken(app);
                setToken(sessionToken);
            } catch (error) {
                console.error("Error fetching token:", error);
            }
        };

        if (app) {
            fetchToken();
        }
    }, [app]);

    const handleNavigation = (url, external = false) => {
        if (external) {
            window.open(url, "_blank");
        } else {
            router.visit(url);
        }
    };

    // Loading state
    if (loading) {
        return (
            <Box padding="400" textAlign="center">
                <Spinner size="small" />
                <Box paddingBlockStart="200">
                    <Text variant="caption" tone="subdued">
                        Loading navigation...
                    </Text>
                </Box>
            </Box>
        );
    }

    // Error state
    if (error) {
        return (
            <Box padding="400" textAlign="center">
                <Text variant="bodyMd" tone="critical">
                    Failed to load navigation
                </Text>
            </Box>
        );
    }

    const { navigation = {} } = sidebarConfig;
    const { primary = [], secondary = [] } = navigation;

    // Build navigation items from config
    const buildNavigationItems = (items) => {
        return items.map(item => {
            const icon = iconMap[item.icon] || HomeIcon;
            const isSelected = currentPath === item.url || 
                              (item.subItems && item.subItems.some(sub => currentPath === sub.url));
            
            const baseItem = {
                key: item.id,
                label: item.label,
                icon: icon,
                selected: isSelected,
            };

            if (item.subItems) {
                // Item with sub-navigation
                baseItem.subNavigationItems = item.subItems.map(subItem => ({
                    key: subItem.id,
                    label: subItem.label,
                    url: subItem.url,
                    disabled: subItem.disabled || false,
                    onClick: () => handleNavigation(subItem.url, subItem.external)
                }));
            } else {
                // Simple item
                baseItem.url = item.url;
                baseItem.onClick = () => handleNavigation(item.url, item.external);
            }

            return baseItem;
        });
    };

    const primaryNavigation = buildNavigationItems(primary);
    const secondaryNavigation = buildNavigationItems(secondary.filter(item => 
        item.enabled !== false
    ));
  

    return (
        <Box>
            <Navigation location={currentPath}>
                <Navigation.Section 
                    items={primaryNavigation} 
                />
                {secondaryNavigation.length > 0 && (
                    <Navigation.Section
                        title="Support & Help"
                        items={secondaryNavigation}
                    />
                )}
            </Navigation>
        </Box>
    );
};

export default Sidebar;
