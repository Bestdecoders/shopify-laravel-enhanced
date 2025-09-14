import { useState, useEffect } from "react";
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
    Divider,
    Spinner,
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
            url: "mailto:support@bestdecoders.com",
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
            url: "https://wa.me/8801720015859",
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
    const [myRequests, setMyRequests] = useState([]);
    const [isLoadingRequests, setIsLoadingRequests] = useState(false);
    const [latestRequestId, setLatestRequestId] = useState(null);
    const axios = useAxios();

    const handleExpectationSubmit = async () => {
        if (!expectation.trim()) {
            setError("Please describe your issue or request.");
            return;
        }

        setError(null);
        setIsSubmitting(true);

        try {
            const response = await axios.post("/support/submit/expectation", { expectation });
            setExpectation("");
            setToastContent(
                "Your request has been submitted successfully! Our team will respond within 24 hours."
            );
            setToastActive(true);

            if (response.data.expectation_id) {
                setLatestRequestId(response.data.expectation_id);
                // Refresh requests to show the new one
                fetchMyReplies(response.data.expectation_id);
            }
        } catch (err) {
            console.error("Failed to submit expectation:", err);
            setError(
                "Failed to submit request. Please try again or contact us directly."
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    const fetchMyReplies = async (expectationId) => {
        if (!expectationId) return;

        setIsLoadingRequests(true);
        try {
            const response = await axios.get(`/support/replies/${expectationId}`);
            if (response.data.success) {
                setMyRequests([response.data]);
            }
        } catch (err) {
            console.error("Failed to fetch replies:", err);
        } finally {
            setIsLoadingRequests(false);
        }
    };

    // Load latest request on component mount if we have one
    useEffect(() => {
        if (latestRequestId) {
            fetchMyReplies(latestRequestId);
        }
    }, [latestRequestId]);

    const handleChannelAction = (channel) => {
        if (channel.action.type === "external") {
            window.open(channel.action.url, "_blank");
        } else {
            router.visit(channel.action.url);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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
                            We're here to help you succeed with Table of
                            Contents
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
                                    or what you'd like the Table of Contents to
                                    look like. The more details you provide, the
                                    better we can help you.
                                </Text>

                                <TextField
                                    label="Describe your issue or request"
                                    value={expectation}
                                    onChange={setExpectation}
                                    multiline={6}
                                    error={error}
                                    placeholder="Example: I'd like the TOC to appear as a floating sidebar on the right, be collapsible, and match my theme colors..."
                                    helpText="Include details like: position preferences, styling requirements, specific issues you're facing, or custom functionality you need."
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

                    {/* Recent Support Requests and Replies */}
                    {myRequests.length > 0 && (
                        <Box paddingBlockStart="600">
                            <Card sectioned>
                                <Box padding="400">
                                    <BlockStack gap="400">
                                        <InlineStack gap="200" align="center">
                                            <Icon source={ChatIcon} />
                                            <Text variant="headingLg" as="h2">
                                                Your Recent Support Request
                                            </Text>
                                        </InlineStack>

                                        {isLoadingRequests ? (
                                            <Box padding="400" style={{ textAlign: "center" }}>
                                                <Spinner size="large" />
                                            </Box>
                                        ) : (
                                            myRequests.map((request) => (
                                                <Card key={request.expectation?.created_at || 'request'} sectioned>
                                                    <Box padding="300">
                                                        <BlockStack gap="300">
                                                            {/* Original Request */}
                                                            <Box>
                                                                <InlineStack gap="200" align="space-between">
                                                                    <Text variant="headingSm" fontWeight="medium">
                                                                        Your Request
                                                                    </Text>
                                                                    <Badge
                                                                        tone={
                                                                            request.status === 'replied' ? 'success' :
                                                                            request.status === 'resolved' ? 'info' :
                                                                            request.status === 'closed' ? 'subdued' : 'warning'
                                                                        }
                                                                    >
                                                                        {request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                                                                    </Badge>
                                                                </InlineStack>
                                                                <Text variant="bodySm" tone="subdued">
                                                                    {formatDate(request.created_at)}
                                                                </Text>
                                                                <Box paddingBlockStart="200">
                                                                    <Text variant="bodyMd">
                                                                        {request.expectation?.message || 'No message available'}
                                                                    </Text>
                                                                </Box>
                                                            </Box>

                                                            {/* Replies */}
                                                            {request.replies && request.replies.length > 0 && (
                                                                <>
                                                                    <Divider />
                                                                    <BlockStack gap="300">
                                                                        <Text variant="headingSm" fontWeight="medium">
                                                                            Support Team Replies
                                                                        </Text>
                                                                        {request.replies.map((reply, index) => (
                                                                            <Box key={index} padding="300"
                                                                                 style={{
                                                                                     backgroundColor: '#f8f9fa',
                                                                                     borderRadius: '8px',
                                                                                     borderLeft: '4px solid #667eea'
                                                                                 }}
                                                                            >
                                                                                <BlockStack gap="200">
                                                                                    <InlineStack gap="200" align="space-between">
                                                                                        <Text variant="bodySm" fontWeight="medium" tone="subdued">
                                                                                            {reply.admin_email}
                                                                                        </Text>
                                                                                        <Text variant="bodySm" tone="subdued">
                                                                                            {formatDate(reply.created_at)}
                                                                                        </Text>
                                                                                    </InlineStack>
                                                                                    <Text variant="bodyMd">
                                                                                        {reply.message}
                                                                                    </Text>
                                                                                </BlockStack>
                                                                            </Box>
                                                                        ))}
                                                                    </BlockStack>
                                                                </>
                                                            )}

                                                            {request.status === 'pending' && (
                                                                <Box padding="200"
                                                                     style={{
                                                                         backgroundColor: '#fff3cd',
                                                                         borderRadius: '8px',
                                                                         textAlign: 'center'
                                                                     }}
                                                                >
                                                                    <Text variant="bodySm" tone="subdued">
                                                                        ⏳ Waiting for support team response...
                                                                    </Text>
                                                                </Box>
                                                            )}
                                                        </BlockStack>
                                                    </Box>
                                                </Card>
                                            ))
                                        )}
                                    </BlockStack>
                                </Box>
                            </Card>
                        </Box>
                    )}

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
                                                    url="mailto:support@bestdecoders.com"
                                                    external
                                                >
                                                    support@bestdecoders.com
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
                                                    url="https://wa.me/8801720015859"
                                                    external
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
                                        committed to making Table of Contents
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
