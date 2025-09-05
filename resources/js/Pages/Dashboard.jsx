import { useState, useEffect } from "react";
import {
    Page,
    InlineGrid,
    BlockStack,
    Card,
    Box,
    Text,
    Button,
    Spinner,
    Badge,
    Icon,
    InlineStack,
    Divider,
    EmptyState
} from "@shopify/polaris";
import {
    HomeIcon,
    SettingsIcon,
    ColorIcon,
    CreditCardIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    QuestionCircleIcon,
    CheckCircleIcon
} from "@shopify/polaris-icons";
import Sidebar from "../components/sidebar";
import { router } from "@inertiajs/react";

// =============================================================================
// ⚠️  DASHBOARD CONFIGURATION - Data is loaded from JSON
// =============================================================================
// Dashboard content is loaded from resources/data/dashboard-config.json
// To customize for your app, edit the JSON file instead of this component
// =============================================================================

const iconMap = {
    HomeIcon,
    SettingsIcon,
    ColorIcon,
    CreditCardIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    QuestionCircleIcon,
    CheckCircleIcon
};

export default function Dashboard() {
    const [dashboardConfig, setDashboardConfig] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Load dashboard configuration
    useEffect(() => {
        const loadDashboardConfig = async () => {
            try {
                setLoading(true);
                const response = await fetch('/resources/data/dashboard-config.json');
                if (!response.ok) {
                    throw new Error('Failed to load dashboard config');
                }
                const config = await response.json();
                setDashboardConfig(config);
            } catch (err) {
                console.error('Error loading dashboard config:', err);
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadDashboardConfig();
    }, []);

    const handleNavigation = (url) => {
        router.visit(url);
    };

    const getTrendIcon = (trend) => {
        switch (trend) {
            case 'positive': return TrendingUpIcon;
            case 'negative': return TrendingDownIcon;
            default: return null;
        }
    };

    const getTrendColor = (trend) => {
        switch (trend) {
            case 'positive': return 'success';
            case 'negative': return 'critical';
            default: return 'subdued';
        }
    };

    // Loading state
    if (loading) {
        return (
            <Page title="Dashboard" fullWidth>
                <Box padding="600" textAlign="center">
                    <Spinner size="large" />
                    <Box paddingBlockStart="300">
                        <Text variant="bodyMd" tone="subdued">
                            Loading dashboard...
                        </Text>
                    </Box>
                </Box>
            </Page>
        );
    }

    // Error state
    if (error) {
        return (
            <Page title="Dashboard" fullWidth>
                <InlineGrid columns={{ xs: 1, md: '0.5fr 3.5fr' }} gap="400">
                    <BlockStack gap="400">
                        <Card sectioned>
                            <Sidebar />
                        </Card>
                    </BlockStack>
                    <BlockStack gap="400">
                        <Card sectioned>
                            <EmptyState
                                heading="Failed to load dashboard"
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                            >
                                <p>We couldn't load the dashboard configuration. Please try refreshing the page.</p>
                                <p>Error: {error}</p>
                            </EmptyState>
                        </Card>
                    </BlockStack>
                </InlineGrid>
            </Page>
        );
    }

    const { appConfig = {}, dashboard = {} } = dashboardConfig;
    const {
        welcomeCard = {},
        quickActions = [],
        statsCards = [],
        helpCard = {}
    } = dashboard;

    return (
        <Page title="Dashboard" fullWidth>
            <InlineGrid columns={{ xs: 1, md: '0.5fr 3.5fr' }} gap="400">
                {/* Sidebar */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar />
                    </Card>
                </BlockStack>

                {/* Main Dashboard Content */}
                <BlockStack gap="400">
                    {/* Welcome Card */}
                    {welcomeCard.title && (
                        <Card sectioned>
                            <Box padding="400">
                                <BlockStack gap="400">
                                    <BlockStack gap="200">
                                        <Text variant="headingLg" as="h1">
                                            {welcomeCard.title.replace('{appName}', appConfig.appName || 'Your App')}
                                        </Text>
                                        <Text variant="bodyMd" tone="subdued">
                                            {welcomeCard.subtitle.replace('{appDescription}', appConfig.appDescription || 'this app')}
                                        </Text>
                                    </BlockStack>
                                    
                                    {welcomeCard.showGettingStarted && (
                                        <Box>
                                            <Button primary onClick={() => handleNavigation('/setup')}>
                                                Get Started
                                            </Button>
                                        </Box>
                                    )}
                                </BlockStack>
                            </Box>
                        </Card>
                    )}

                    {/* Stats Cards */}
                    {statsCards.length > 0 && (
                        <InlineGrid columns={{ xs: 1, md: 3 }} gap="400">
                            {statsCards.map((stat) => {
                                const TrendIcon = getTrendIcon(stat.trend);
                                const trendColor = getTrendColor(stat.trend);
                                
                                return (
                                    <Card key={stat.id} sectioned>
                                        <Box padding="300">
                                            <BlockStack gap="300">
                                                <Text variant="headingSm" as="h3" tone="subdued">
                                                    {stat.title}
                                                </Text>
                                                <InlineStack gap="200" align="space-between">
                                                    <Text variant="headingLg" as="p">
                                                        {stat.value}
                                                    </Text>
                                                    {stat.change && TrendIcon && (
                                                        <InlineStack gap="100" align="center">
                                                            <Icon source={TrendIcon} tone={trendColor} />
                                                            <Text variant="bodySm" tone={trendColor}>
                                                                {stat.change}
                                                            </Text>
                                                        </InlineStack>
                                                    )}
                                                </InlineStack>
                                                {stat.description && (
                                                    <Text variant="caption" tone="subdued">
                                                        {stat.description}
                                                    </Text>
                                                )}
                                            </BlockStack>
                                        </Box>
                                    </Card>
                                );
                            })}
                        </InlineGrid>
                    )}

                    {/* Quick Actions */}
                    {quickActions.length > 0 && (
                        <Card sectioned>
                            <Box padding="400">
                                <BlockStack gap="400">
                                    <Text variant="headingMd" as="h2">
                                        Quick Actions
                                    </Text>
                                    <InlineGrid columns={{ xs: 1, md: 2, lg: 3 }} gap="400">
                                        {quickActions.filter(action => action.enabled).map((action) => {
                                            const ActionIcon = iconMap[action.icon] || SettingsIcon;
                                            
                                            return (
                                                <Card key={action.id}>
                                                    <Box padding="400">
                                                        <BlockStack gap="300">
                                                            <InlineStack gap="300" align="start">
                                                                <Icon source={ActionIcon} />
                                                                <BlockStack gap="200">
                                                                    <Text variant="headingSm" as="h3">
                                                                        {action.title}
                                                                    </Text>
                                                                    <Text variant="bodySm" tone="subdued">
                                                                        {action.description}
                                                                    </Text>
                                                                </BlockStack>
                                                            </InlineStack>
                                                            <Box paddingBlockStart="200">
                                                                <Button 
                                                                    primary={action.primary}
                                                                    onClick={() => handleNavigation(action.url)}
                                                                    fullWidth
                                                                    size="medium"
                                                                >
                                                                    {action.title}
                                                                </Button>
                                                            </Box>
                                                        </BlockStack>
                                                    </Box>
                                                </Card>
                                            );
                                        })}
                                    </InlineGrid>
                                </BlockStack>
                            </Box>
                        </Card>
                    )}

                    {/* Help Card */}
                    {helpCard.title && (
                        <Card sectioned>
                            <Box padding="400" textAlign="center">
                                <BlockStack gap="400">
                                    <Icon source={QuestionCircleIcon} />
                                    <BlockStack gap="200">
                                        <Text variant="headingMd" as="h3">
                                            {helpCard.title}
                                        </Text>
                                        <Text variant="bodyMd" tone="subdued">
                                            {helpCard.description}
                                        </Text>
                                    </BlockStack>
                                    <InlineStack gap="300" align="center">
                                        {helpCard.actions?.map((action, index) => (
                                            <Button
                                                key={index}
                                                primary={action.primary}
                                                onClick={() => handleNavigation(action.url)}
                                            >
                                                {action.label}
                                            </Button>
                                        ))}
                                    </InlineStack>
                                </BlockStack>
                            </Box>
                        </Card>
                    )}
                </BlockStack>
            </InlineGrid>
        </Page>
    );
}