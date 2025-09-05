import { useState } from "react";
import {
    Card,
    Text,
    TextField,
    Button,
    Link,
    Box,
    InlineStack,
    BlockStack,
    Toast,
    Frame,
    Badge,
    Icon,
} from "@shopify/polaris";
import {
    EmailIcon,
    ChatIcon,
    QuestionCircleIcon,
    DockFloatingIcon,
    CheckCircleIcon,
    NoteIcon,
} from "@shopify/polaris-icons";
import { router } from "@inertiajs/react";
import { useAxios } from "../hooks/useAxios";

// =============================================================================
// ⚠️  IMPORTANT: CUSTOMIZE THESE VALUES FOR YOUR APP
// =============================================================================
// This configuration section contains all app-specific references that need
// to be updated when using this support component in different applications.
// Simply modify the values below to match your app's details.
// =============================================================================

const APP_CONFIG = {
    appName: "Your App Name", // REQUIRED: Change this to your app's display name
    supportEmail: "support@yourdomain.com", // REQUIRED: Your support email address  
    whatsappNumber: "1234567890", // OPTIONAL: Your WhatsApp number (without + or country code)
    appDescription: "your app", // REQUIRED: Short generic reference to your app
    featureDescription: "the main functionality", // REQUIRED: Description of your app's main feature
};

// =============================================================================
// END OF CONFIGURATION - No changes needed below this line
// =============================================================================

const supportChannels = [
    {
        id: "form",
        title: "Submit a Request",
        description: "Describe your issue or request in detail",
        icon: DockFloatingIcon,
        color: "info",
        recommended: true,
    },
    {
        id: "email",
        title: "Email Support",
        description: "Get help via email within 24 hours",
        icon: EmailIcon,
        color: "subdued",
        action: {
            type: "external",
            url: `mailto:${APP_CONFIG.supportEmail}`,
            label: "Send Email",
        },
    },
    {
        id: "whatsapp",
        title: "WhatsApp Chat",
        description: "Get instant help via WhatsApp",
        icon: ChatIcon,
        color: "success",
        action: {
            type: "external",
            url: `https://wa.me/${APP_CONFIG.whatsappNumber}`,
            label: "Start Chat",
        },
    },
    {
        id: "faq",
        title: "FAQ & Documentation",
        description: "Find answers to common questions",
        icon: QuestionCircleIcon,
        color: "warning",
        action: {
            type: "internal",
            url: "/faq",
            label: "View FAQ",
        },
    },
];

export default function Support() {
    const [expectation, setExpectation] = useState("");
    const [error, setError] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [toastActive, setToastActive] = useState(false);
    const [toastContent, setToastContent] = useState("");
    const axios = useAxios();

    const handleExpectationSubmit = async () => {
        if (!expectation.trim()) {
            setError("Please describe your issue or request.");
            return;
        }

        setError(null);
        setIsSubmitting(true);

        try {
            await axios.post("/submit/expectation", { expectation });
            setExpectation("");
            setToastContent(
                "Your request has been submitted successfully! Our team will respond within 24 hours."
            );
            setToastActive(true);
        } catch (err) {
            console.error("Failed to submit expectation:", err);
            setError(
                "Failed to submit request. Please try again or contact us directly."
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleChannelAction = (channel) => {
        if (channel.action.type === "external") {
            window.open(channel.action.url, "_blank");
        } else {
            router.visit(channel.action.url);
        }
    };

    return (
        <Frame>
            <Box>
                {/* Header Section */}
                <Box
                    padding="600"
                    background="bg-surface-secondary"
                    style={{ textAlign: "center" }}
                >
                    <BlockStack gap="400">
                        <Text variant="displayMd" as="h1">
                            Support Center
                        </Text>
                        <Text variant="bodyLg" tone="subdued">
                            We're here to help you succeed with {APP_CONFIG.appName}
                        </Text>
                    </BlockStack>
                </Box>

                <Box padding="400">
                    {/* Support Channels Overview */}
                    <Box paddingBlockEnd="600">
                        <Card sectioned>
                            <Box padding="400">
                                <Text
                                    variant="headingMd"
                                    as="h2"
                                    paddingBlockEnd="400"
                                >
                                    How can we help you today?
                                </Text>
                                <InlineStack gap="400" wrap={true}>
                                    {supportChannels.map((channel) => (
                                        <Box
                                            key={channel.id}
                                            minWidth="250px"
                                            style={{ flex: "1" }}
                                        >
                                            <Card sectioned>
                                                <Box padding="300">
                                                    <BlockStack gap="300">
                                                        <InlineStack
                                                            gap="200"
                                                            align="space-between"
                                                        >
                                                            <InlineStack
                                                                gap="200"
                                                                align="center"
                                                            >
                                                                <Icon
                                                                    source={
                                                                        channel.icon
                                                                    }
                                                                />
                                                                <Text
                                                                    variant="headingSm"
                                                                    fontWeight="medium"
                                                                >
                                                                    {
                                                                        channel.title
                                                                    }
                                                                </Text>
                                                            </InlineStack>
                                                            {channel.recommended && (
                                                                <Badge
                                                                    tone="info"
                                                                    size="small"
                                                                >
                                                                    Recommended
                                                                </Badge>
                                                            )}
                                                        </InlineStack>

                                                        <Text
                                                            variant="bodySm"
                                                            tone="subdued"
                                                        >
                                                            {
                                                                channel.description
                                                            }
                                                        </Text>

                                                        {channel.action && (
                                                            <Button
                                                                onClick={() =>
                                                                    handleChannelAction(
                                                                        channel
                                                                    )
                                                                }
                                                                size="medium"
                                                                fullWidth
                                                            >
                                                                {
                                                                    channel
                                                                        .action
                                                                        .label
                                                                }
                                                            </Button>
                                                        )}
                                                    </BlockStack>
                                                </Box>
                                            </Card>
                                        </Box>
                                    ))}
                                </InlineStack>
                            </Box>
                        </Card>
                    </Box>

                    {/* Main Support Form */}
                    <Card sectioned>
                        <Box padding="400">
                            <BlockStack gap="400">
                                <InlineStack gap="200" align="center">
                                    <Icon source={NoteIcon} />
                                    <Text variant="headingLg" as="h2">
                                        Submit a Support Request
                                    </Text>
                                </InlineStack>

                                <Text variant="bodyMd" tone="subdued">
                                    Please describe your issue, feature request,
                                    or what you'd like {APP_CONFIG.featureDescription} to
                                    look like. The more details you provide, the
                                    better we can help you.
                                </Text>

                                <TextField
                                    label="Describe your issue or request"
                                    value={expectation}
                                    onChange={setExpectation}
                                    multiline={6}
                                    error={error}
                                    placeholder={`Example: I'd like ${APP_CONFIG.featureDescription} to appear in a specific way, have certain styling, and match my theme colors...`}
                                    helpText="Include details like: preferences, styling requirements, specific issues you're facing, or custom functionality you need."
                                />

                                <InlineStack gap="300">
                                    <Button
                                        primary
                                        onClick={handleExpectationSubmit}
                                        loading={isSubmitting}
                                        size="large"
                                    >
                                        Submit Request
                                    </Button>
                                    <Button
                                        onClick={() => router.visit("/docs")}
                                        size="large"
                                    >
                                        View Documentation
                                    </Button>
                                </InlineStack>
                            </BlockStack>
                        </Box>
                    </Card>

                    {/* Contact Information */}
                    <Box paddingBlockStart="600">
                        <Card sectioned>
                            <Box padding="400">
                                <BlockStack gap="400">
                                    <Text variant="headingMd" as="h3">
                                        Direct Contact Information
                                    </Text>

                                    <InlineStack gap="600" wrap={true}>
                                        <Box>
                                            <BlockStack gap="200">
                                                <InlineStack
                                                    gap="200"
                                                    align="center"
                                                >
                                                    <Icon source={EmailIcon} />
                                                    <Text
                                                        variant="headingSm"
                                                        fontWeight="medium"
                                                    >
                                                        Email Support
                                                    </Text>
                                                </InlineStack>
                                                <Text
                                                    variant="bodySm"
                                                    tone="subdued"
                                                >
                                                    Response within 24 hours
                                                </Text>
                                                <Link
                                                    url={`mailto:${APP_CONFIG.supportEmail}`}
                                                    target="_blank"
                                                >
                                                    {APP_CONFIG.supportEmail}
                                                </Link>
                                            </BlockStack>
                                        </Box>

                                        <Box>
                                            <BlockStack gap="200">
                                                <InlineStack
                                                    gap="200"
                                                    align="center"
                                                >
                                                    <Icon source={ChatIcon} />
                                                    <Text
                                                        variant="headingSm"
                                                        fontWeight="medium"
                                                    >
                                                        WhatsApp Chat
                                                    </Text>
                                                </InlineStack>
                                                <Text
                                                    variant="bodySm"
                                                    tone="subdued"
                                                >
                                                    Instant messaging support
                                                </Text>
                                                <Link
                                                    url={`https://wa.me/${APP_CONFIG.whatsappNumber}`}
                                                    target="_blank"
                                                >
                                                    Start WhatsApp Chat
                                                </Link>
                                            </BlockStack>
                                        </Box>

                                        <Box>
                                            <BlockStack gap="200">
                                                <InlineStack
                                                    gap="200"
                                                    align="center"
                                                >
                                                    <Icon
                                                        source={NoteIcon}
                                                    />
                                                    <Text
                                                        variant="headingSm"
                                                        fontWeight="medium"
                                                    >
                                                        Documentation
                                                    </Text>
                                                </InlineStack>
                                                <Text
                                                    variant="bodySm"
                                                    tone="subdued"
                                                >
                                                    Self-service help guides
                                                </Text>
                                                <Button
                                                    plain
                                                    onClick={() =>
                                                        router.visit("/docs")
                                                    }
                                                >
                                                    Browse Documentation
                                                </Button>
                                            </BlockStack>
                                        </Box>
                                    </InlineStack>
                                </BlockStack>
                            </Box>
                        </Card>
                    </Box>

                    {/* Support Commitment */}
                    <Box paddingBlockStart="600">
                        <Card sectioned>
                            <Box padding="400" style={{ textAlign: "center" }}>
                                <BlockStack gap="300">
                                    <InlineStack
                                        gap="200"
                                        align="center"
                                        blockAlign="center"
                                    >
                                        <Icon source={CheckCircleIcon} />
                                        <Text variant="headingMd" as="h3">
                                            Our Support Commitment
                                        </Text>
                                    </InlineStack>
                                    <Text variant="bodyMd" tone="subdued">
                                        We take every request seriously and are
                                        committed to making {APP_CONFIG.appName}
                                        work perfectly for your store. Whether
                                        you're setting up for the first time or
                                        need custom functionality, we're here to
                                        help you succeed.
                                    </Text>
                                </BlockStack>
                            </Box>
                        </Card>
                    </Box>
                </Box>
            </Box>

            {toastActive && (
                <Toast
                    content={toastContent}
                    onDismiss={() => setToastActive(false)}
                />
            )}
        </Frame>
    );
}
