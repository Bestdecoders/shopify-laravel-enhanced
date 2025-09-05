import { useState, useEffect } from "react";
import { 
    Card, 
    Text, 
    Button, 
    Spinner, 
    BlockStack, 
    InlineStack, 
    Box,
    Badge,
    List
} from "@shopify/polaris";
import { useAxios } from "../hooks/useAxios";
import { log } from "../hooks/helper";

export function Pricing() {
    const axios = useAxios();
    const [loading, setLoading] = useState(true);
    const [plans, setPlans] = useState([]);
    const [currentPlan, setCurrentPlan] = useState(null);

    useEffect(() => {
        fetchPlans();
    }, []);

    const fetchPlans = async () => {
        try {
            const response = await axios.get("/plan/details");
            log(response.data);
            setPlans(response.data.plans || []);
            setCurrentPlan(response.data.current_plan || null);
        } catch (error) {
            console.error("Failed to fetch plans:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubscribe = async (planId) => {
        try {
            const response = await axios.post("/get/plan/subscription/url", {
                planId,
            });
            log(response.data);
            const { confirmationUrl, forceRedirectUrl } = response.data;

            if (confirmationUrl || forceRedirectUrl) {
                window.location.href = confirmationUrl || forceRedirectUrl;
            }
        } catch (error) {
            console.error("Subscription failed:", error);
        }
    };

    const formatPrice = (plan) => {
        if (plan.price === 0) return "Free";
        const interval = plan.interval === "EVERY_30_DAYS" ? "month" : "year";
        return `$${plan.price}/${interval}`;
    };

    const isCurrentPlan = (planId) => currentPlan?.id === planId;
    const isUpgrade = (plan) => plan.price > (currentPlan?.price || 0);
    const isPopular = (plan) => plan.price > 0 && !isCurrentPlan(plan.id);

    if (loading) {
        return (
            <Box padding="600" textAlign="center">
                <Spinner accessibilityLabel="Loading plans" size="large" />
                <Box paddingBlockStart="300">
                    <Text variant="bodyMd" tone="subdued">
                        Loading pricing plans...
                    </Text>
                </Box>
            </Box>
        );
    }

    if (plans.length === 0) {
        return (
            <Box padding="600" textAlign="center">
                <Text variant="headingMd" as="h2">
                    No Plans Available
                </Text>
                <Box paddingBlockStart="300">
                    <Text variant="bodyMd" tone="subdued">
                        Please contact support for pricing information.
                    </Text>
                </Box>
            </Box>
        );
    }

    return (
        <Box>
            {/* Header Section */}
            <Box padding="600" background="bg-surface-secondary" textAlign="center">
                <BlockStack gap="400">
                    <Text variant="displayMd" as="h1">
                        Choose Your Plan
                    </Text>
                    <Text variant="bodyLg" tone="subdued">
                        Select the perfect plan to enhance your store's article navigation
                    </Text>
                </BlockStack>
            </Box>

            {/* Current Plan Banner */}
            {currentPlan && (
                <Box padding="400">
                    <Card sectioned>
                        <InlineStack gap="300" align="center">
                            <Badge tone="success">Current Plan</Badge>
                            <Text variant="bodyMd">
                                You're currently on the <strong>{currentPlan.name}</strong> plan
                            </Text>
                        </InlineStack>
                    </Card>
                </Box>
            )}

            {/* Pricing Cards */}
            <Box padding="400">
                <InlineStack gap="400" wrap={true} align="stretch">
                    {plans.map((plan) => (
                        <Box key={plan.id} minWidth="300px" style={{ flex: "1" }}>
                            <Card sectioned>
                                <Box padding="400">
                                    <BlockStack gap="400">
                                        {/* Plan Header */}
                                        <Box textAlign="center">
                                            <InlineStack gap="200" align="center" blockAlign="center">
                                                <Text variant="headingLg" as="h3">
                                                    {plan.name}
                                                </Text>
                                                {isCurrentPlan(plan.id) && (
                                                    <Badge tone="success">Current</Badge>
                                                )}
                                                {isPopular(plan) && (
                                                    <Badge tone="info">Popular</Badge>
                                                )}
                                            </InlineStack>
                                        </Box>

                                        {/* Price */}
                                        <Box textAlign="center" padding="300" background="bg-surface-secondary" borderRadius="200">
                                            <Text variant="displaySm" as="p" fontWeight="bold">
                                                {formatPrice(plan)}
                                            </Text>
                                            {plan.price > 0 && (
                                                <Text variant="bodySm" tone="subdued">
                                                    Billed {plan.interval === "EVERY_30_DAYS" ? "monthly" : "annually"}
                                                </Text>
                                            )}
                                        </Box>

                                        {/* Description */}
                                        <Box>
                                            <Text variant="bodyMd" tone="subdued" alignment="center">
                                                {plan.terms || "Perfect for getting started with Table of Contents"}
                                            </Text>
                                        </Box>

                                        {/* Features (if available) */}
                                        {plan.features && plan.features.length > 0 && (
                                            <Box>
                                                <Text variant="headingSm" as="h4">
                                                    Features included:
                                                </Text>
                                                <Box paddingBlockStart="200">
                                                    <List type="bullet">
                                                        {plan.features.map((feature, index) => (
                                                            <List.Item key={index}>
                                                                <Text variant="bodySm">{feature}</Text>
                                                            </List.Item>
                                                        ))}
                                                    </List>
                                                </Box>
                                            </Box>
                                        )}

                                        {/* Action Button */}
                                        <Box paddingBlockStart="300">
                                            {isCurrentPlan(plan.id) ? (
                                                <Button 
                                                    fullWidth 
                                                    disabled 
                                                    size="large"
                                                >
                                                    Current Plan
                                                </Button>
                                            ) : (
                                                <Button
                                                    fullWidth
                                                    primary={isUpgrade(plan)}
                                                    size="large"
                                                    onClick={() => handleSubscribe(plan.id)}
                                                >
                                                    {isUpgrade(plan) ? "Upgrade Plan" : "Switch Plan"}
                                                </Button>
                                            )}
                                        </Box>
                                    </BlockStack>
                                </Box>
                            </Card>
                        </Box>
                    ))}
                </InlineStack>
            </Box>

            {/* Additional Information */}
            <Box padding="400">
                <Card sectioned>
                    <Box padding="400" textAlign="center">
                        <BlockStack gap="300">
                            <Text variant="headingMd" as="h3">
                                Need Help Choosing?
                            </Text>
                            <Text variant="bodyMd" tone="subdued">
                                Our team is here to help you find the perfect plan for your needs
                            </Text>
                            <Box paddingBlockStart="300">
                                <InlineStack gap="300" align="center">
                                    <Button onClick={() => window.location.href = '/support'}>
                                        Contact Support
                                    </Button>
                                    <Button plain onClick={() => window.location.href = '/docs'}>
                                        View Documentation
                                    </Button>
                                </InlineStack>
                            </Box>
                        </BlockStack>
                    </Box>
                </Card>
            </Box>
        </Box>
    );
}
