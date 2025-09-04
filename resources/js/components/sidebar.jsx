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
} from "@shopify/polaris-icons";
import { getSessionToken } from "@shopify/app-bridge-utils";
import { useAppBridge } from "@shopify/app-bridge-react";
import { select } from "@shopify/app-bridge/actions/ResourcePicker";
import { disable } from "@shopify/app-bridge/actions/LeaveConfirmation";

const Sidebar = ({
    backendData = {
        somethingNew: true,
        extensionActivated: false,
        extensionInstalled: true,
        liveChatEnabled: false,
        moveable: false,
    },
}) => {
    const app = useAppBridge(); // Initialize App Bridge
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
                const sessionToken = await getSessionToken(app); // Fetch Shopify session token
                setToken(sessionToken);
            } catch (error) {
                console.error("Error fetching token:", error);
            }
        };

        fetchToken();
    }, [app]);

    // Update URLs dynamically with host and token
    const updateUrlWithHostAndToken = (url) => {
        if (url === "#") {
            return "#"; // Do not update placeholder URLs
        }

        if (!host || !token) {
            return url; // Return original URL if host or token is not available
        }

        return `${url}?host=${host}&token=${token}`; // Append host and token to the URL
    };

    // Navigation items with updated URLs
    const primaryNavigation = [
      {
          url: updateUrlWithHostAndToken("/home"),
          label: "Dashboard",
          icon: HomeIcon,
          key: "dashboard", // Unique key
      },
      {
          url: "#",
          label: "Setup",
          icon: CodeIcon,
          selected: true,
          key: "setup", // Unique key
          subNavigationItems: [
              {
                  url: "https://your-external-activation-link.com", // External link to activate the extension
                  label: "Activate Theme Extension",
                  target: "_blank", // Open in a new tab
                  key: "activate-extension", // Unique key
              },
              {
                  url: updateUrlWithHostAndToken("/about"),
                  label: "Select Display Style", // Choose display style (sidebar, button, popup, inline)
                  key: "choose-style", // Unique key
              },
              {
                  url: updateUrlWithHostAndToken("/create-sizechart"),
                  label: "Create Size Chart", // Create size chart
                  key: "create-sizechart", // Unique key
              },
          ],
      },
      {
          url: "#",
          label: "Import/Export",
          icon: UploadIcon,
          key: "import-export", // Unique key
          subNavigationItems: [
              {
                  url: updateUrlWithHostAndToken("/import"),
                  label: "Import Data",
                  disabled: !backendData.moveable,
                  key: "import-data", // Unique key
              },
              {
                  url: updateUrlWithHostAndToken("/export"),
                  label: "Export Data",
                  disabled: !backendData.moveable,
                  key: "export-data", // Unique key
              },
          ],
      },
  ];
  
  const secondaryNavigation = [
      {
          url: updateUrlWithHostAndToken("/help-center"),
          label: "Help Center",
          icon: QuestionCircleIcon,
          key: "help-center", // Unique key
      },
      backendData.liveChatEnabled && {
          url: updateUrlWithHostAndToken("/live-chat"),
          label: "Start a Live Chat",
          icon: ChatIcon,
          key: "live-chat", // Unique key
      },
      {
          url: updateUrlWithHostAndToken("/feedback"),
          label: "Feedback",
          icon: ThumbsUpIcon,
          key: "feedback", // Unique key
      },
      backendData.somethingNew && {
          url: updateUrlWithHostAndToken("/whats-new"),
          label: "What's New",
          icon: NotificationIcon,
          key: "whats-new", // Unique key
      },
  ].filter(Boolean); // Ensure no null values
  

    return (
        <div className="fixed left-10 top-20 bor">
            <Frame >
                <Navigation location="/" >
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
