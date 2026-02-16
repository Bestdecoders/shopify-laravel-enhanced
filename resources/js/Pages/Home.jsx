import React, { useState } from "react";
import { Page, BlockStack, InlineStack, Card, Text, Badge, InlineGrid, Banner } from '@shopify/polaris';

const Home = () => {
    const [bannerVisible, setBannerVisible] = useState(true);

    return (
        <Page title="Dashboard">
            <BlockStack gap="400">
                {/* Dismissible banner for announcements */}
                {bannerVisible && (
                    <Banner
                        dismissable
                        onDismiss={() => setBannerVisible(false)}
                        status="info"
                        title="Welcome to Easy Table Of Contents!"
                    >
                        <p>Your table of contents is ready to use. Configure your settings to get started.</p>
                    </Banner>
                )}

                {/* Status card showing app is working */}
                <Card>
                    <BlockStack gap="200">
                        <InlineStack align="space-between">
                            <Text variant="headingMd" as="h2">App Status</Text>
                            <Badge status="success">Active</Badge>
                        </InlineStack>
                        <Text variant="bodyMd" as="p">Your table of contents is ready to use.</Text>
                    </BlockStack>
                </Card>

                {/* Quick metrics/stats */}
                <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
                    <Card>
                        <BlockStack gap="100">
                            <Text variant="headingSm" as="h3">Total Tables</Text>
                            <Text variant="headingLg" as="p">0</Text>
                        </BlockStack>
                    </Card>
                    <Card>
                        <BlockStack gap="100">
                            <Text variant="headingSm" as="h3">Active on Pages</Text>
                            <Text variant="headingLg" as="p">0</Text>
                        </BlockStack>
                    </Card>
                </InlineGrid>

                {/* Quick actions */}
                <Card>
                    <BlockStack gap="300">
                        <Text variant="headingMd" as="h2">Quick Actions</Text>
                        <Text variant="bodyMd" as="p" tone="subdued">
                            Use the navigation menu above to access Settings and About pages.
                        </Text>
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
};

export default Home;
