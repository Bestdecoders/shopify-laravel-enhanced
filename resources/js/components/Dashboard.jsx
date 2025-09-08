import React, { useState, useEffect } from "react";
import {
    Card,
    Text,
    BlockStack,
    InlineStack,
    Box,
    Button,
    Badge,
    Spinner,
    EmptyState,
    Banner,
    Icon,
} from "@shopify/polaris";
import {
    HomeIcon,
    OrderIcon,
    ProductIcon,
    ChartVerticalIcon,
    SettingsIcon,
    PlusIcon,
} from "@shopify/polaris-icons";
import { useAxios } from "../hooks/useAxios";

const Dashboard = () => {
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({
        totalProducts: 0,
        totalOrders: 0,
        totalRevenue: 0,
        activeCharts: 0,
    });
    const [recentActivity, setRecentActivity] = useState([]);
    const axios = useAxios();

    useEffect(() => {
        // Simulate loading dashboard data
        setTimeout(() => {
            setStats({
                totalProducts: 150,
                totalOrders: 89,
                totalRevenue: 12450,
                activeCharts: 5,
            });
            setRecentActivity([
                { id: 1, action: "Size chart created", time: "2 hours ago", type: "success" },
                { id: 2, action: "Product assigned to chart", time: "4 hours ago", type: "info" },
                { id: 3, action: "Chart template updated", time: "1 day ago", type: "warning" },
            ]);
            setLoading(false);
        }, 1000);
    }, []);

    const StatCard = ({ title, value, icon, trend }) => (
        <Card>
            <Box padding="400">
                <InlineStack align="space-between">
                    <BlockStack gap="200">
                        <Text variant="bodySm" tone="subdued">
                            {title}
                        </Text>
                        <Text variant="headingLg" as="h3">
                            {value}
                        </Text>
                        {trend && (
                            <Badge tone={trend > 0 ? "success" : "critical"}>
                                {trend > 0 ? "+" : ""}{trend}%
                            </Badge>
                        )}
                    </BlockStack>
                    <Icon source={icon} tone="base" />
                </InlineStack>
            </Box>
        </Card>
    );

    const QuickActionCard = ({ title, description, action, icon }) => (
        <Card>
            <Box padding="400">
                <BlockStack gap="300">
                    <InlineStack align="space-between">
                        <Icon source={icon} tone="base" />
                        <Button size="slim" onClick={action}>
                            Get Started
                        </Button>
                    </InlineStack>
                    <BlockStack gap="200">
                        <Text variant="headingSm" as="h4">
                            {title}
                        </Text>
                        <Text variant="bodySm" tone="subdued">
                            {description}
                        </Text>
                    </BlockStack>
                </BlockStack>
            </Box>
        </Card>
    );

    if (loading) {
        return (
            <Box padding="600">
                <InlineStack align="center">
                    <Spinner accessibilityLabel="Loading dashboard" size="large" />
                </InlineStack>
            </Box>
        );
    }

    return (
        <BlockStack gap="400">
            {/* Welcome Banner */}
            <Banner>
                <Text variant="headingMd" as="h2">
                    Welcome to your Size Chart Dashboard! 👋
                </Text>
                <Text variant="bodyMd">
                    Get started by creating your first size chart or explore our quick setup options below.
                </Text>
            </Banner>

            {/* Stats Overview */}
            <Text variant="headingLg" as="h2">
                Overview
            </Text>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))", gap: "1rem" }}>
                <StatCard
                    title="Total Products"
                    value={stats.totalProducts}
                    icon={ProductIcon}
                    trend={12}
                />
                <StatCard
                    title="Active Size Charts"
                    value={stats.activeCharts}
                    icon={ChartVerticalIcon}
                    trend={8}
                />
                <StatCard
                    title="Total Orders"
                    value={stats.totalOrders}
                    icon={OrderIcon}
                    trend={-3}
                />
                <StatCard
                    title="Revenue"
                    value={`$${stats.totalRevenue.toLocaleString()}`}
                    icon={HomeIcon}
                    trend={15}
                />
            </div>

            {/* Quick Actions */}
            <Text variant="headingLg" as="h2">
                Quick Actions
            </Text>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(300px, 1fr))", gap: "1rem" }}>
                <QuickActionCard
                    title="Create Size Chart"
                    description="Build your first size chart with our easy-to-use table editor."
                    icon={PlusIcon}
                    action={() => console.log("Navigate to create chart")}
                />
                <QuickActionCard
                    title="Import Data"
                    description="Import existing size charts from CSV or other formats."
                    icon={SettingsIcon}
                    action={() => console.log("Navigate to import")}
                />
                <QuickActionCard
                    title="Setup Display Style"
                    description="Choose how size charts appear on your store (popup, inline, etc.)."
                    icon={SettingsIcon}
                    action={() => console.log("Navigate to display settings")}
                />
            </div>

            {/* Recent Activity */}
            <Text variant="headingLg" as="h2">
                Recent Activity
            </Text>
            <Card>
                <BlockStack gap="300">
                    {recentActivity.length > 0 ? (
                        recentActivity.map((activity) => (
                            <Box key={activity.id} padding="300">
                                <InlineStack align="space-between">
                                    <BlockStack gap="100">
                                        <Text variant="bodyMd">{activity.action}</Text>
                                        <Text variant="bodySm" tone="subdued">
                                            {activity.time}
                                        </Text>
                                    </BlockStack>
                                    <Badge tone={activity.type === "success" ? "success" : activity.type === "warning" ? "attention" : "info"}>
                                        {activity.type}
                                    </Badge>
                                </InlineStack>
                            </Box>
                        ))
                    ) : (
                        <EmptyState
                            heading="No recent activity"
                            description="Your recent actions will appear here."
                            image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                        />
                    )}
                </BlockStack>
            </Card>
        </BlockStack>
    );
};

export default Dashboard;
