import React, { useEffect, useState } from "react";
import { Frame, Navigation } from "@shopify/polaris";
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
    BookIcon,
} from "@shopify/polaris-icons";
import { useAppBridge } from "@shopify/app-bridge-react";

const Sidebar = ({
    backendData = {
        somethingNew: true,
        extensionActivated: false,
        extensionInstalled: true,
        liveChatEnabled: false,
        moveable: false,
    },
}) => {
    const shopify = useAppBridge(); // Initialize App Bridge
    const [host, setHost] = useState(null);
    const [token, setToken] = useState(null);

    // Fetch and set host and token
    useEffect(() => {
        const currentHost =
            new URLSearchParams(window.location.search).get("host") ||
            window.__SHOPIFY_HOST;

        setHost(currentHost);

        const fetchToken = async () => {
            try {
                if (shopify && shopify.idToken) {
                    const sessionToken = await shopify.idToken();
                    setToken(sessionToken);
                }
            } catch (error) {
                console.warn("Error fetching token for sidebar:", error);
            }
        };

        fetchToken();
    }, [shopify]);

    // Update URLs dynamically with host and token
    const getUrlWithHostAndToken = (url) => {
        if (url === "#") {
            return "#"; // Do not update placeholder URLs
        }

        if (!host) {
            return url; // Return original URL if host is not available
        }

        // For navigation, include both host and token
        const params = new URLSearchParams({ host });
        if (token) {
            params.set("token", token);
        }

        return `${url}?${params.toString()}`;
    };


    // Navigation items with updated URLs
    const primaryNavigation = [
        {
            url: getUrlWithHostAndToken("/home"),
            label: "Dashboard",
            icon: HomeIcon,
            key: "dashboard",
        },
        {
            url: "#",
            label: "Setup",
            icon: CodeIcon,
            selected: true,
            key: "setup",
            subNavigationItems: [
                {
                    url: "https://your-external-activation-link.com",
                    label: "Activate Theme Extension",
                    key: "activate-extension",
                },
                {
                    url: getUrlWithHostAndToken("/about"),
                    label: "Select Display Style",
                    key: "choose-style",
                },
                {
                    url: getUrlWithHostAndToken("/create-sizechart"),
                    label: "Create Size Chart",
                    key: "create-sizechart",
                },
            ],
        },
        {
            url: "#",
            label: "Import/Export",
            icon: UploadIcon,
            key: "import-export",
            subNavigationItems: [
                {
                    url: getUrlWithHostAndToken("/import"),
                    label: "Import Data",
                    disabled: !backendData.moveable,
                    key: "import-data",
                },
                {
                    url: getUrlWithHostAndToken("/export"),
                    label: "Export Data",
                    disabled: !backendData.moveable,
                    key: "export-data",
                },
            ],
        },
    ];

    const secondaryNavigation = [
        {
            url: getUrlWithHostAndToken("/pricing"),
            label: "Pricing",
            icon: ChartVerticalIcon,
            key: "pricing",
        },
        {
            url: getUrlWithHostAndToken("/docs"),
            label: "Documentation",
            icon: BookIcon,
            key: "documentation",
        },
        {
            url: getUrlWithHostAndToken("/faq"),
            label: "FAQ",
            icon: QuestionCircleIcon,
            key: "faq",
        },
        {
            url: getUrlWithHostAndToken("/help-center"),
            label: "Help Center",
            icon: QuestionCircleIcon,
            key: "help-center",
        },
        backendData.liveChatEnabled && {
            url: getUrlWithHostAndToken("/live-chat"),
            label: "Start a Live Chat",
            icon: ChatIcon,
            key: "live-chat",
        },
        {
            url: getUrlWithHostAndToken("/feedback"),
            label: "Feedback",
            icon: ThumbsUpIcon,
            key: "feedback",
        },
        backendData.somethingNew && {
            url: getUrlWithHostAndToken("/whats-new"),
            label: "What's New",
            icon: NotificationIcon,
            key: "whats-new",
        },
    ].filter(Boolean);

    return (
        <div className="fixed left-10 top-20 bor">
            <Frame>
                <Navigation location="/">
                    <Navigation.Section items={primaryNavigation} />
                    <Navigation.Section
                        title="Connect"
                        items={secondaryNavigation}
                    />
                </Navigation>
            </Frame>
        </div>
    );
};

export default Sidebar;
