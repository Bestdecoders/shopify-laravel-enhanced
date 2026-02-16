import React from "react";
import { Page, BlockStack, Card, Text, List, ButtonGroup, Button } from '@shopify/polaris';

const About = () => {
    return (
        <Page title="About">
            <BlockStack gap="400">
                {/* App Info Card */}
                <Card>
                    <BlockStack gap="300">
                        <Text variant="headingLg" as="h1">Easy Table Of Contents</Text>
                        <Text variant="bodyMd" as="p">
                            Automatically generate a beautiful table of contents for your pages and blog posts.
                        </Text>
                        <Text variant="bodyMd" as="p" tone="subdued">
                            Version 1.0.0
                        </Text>
                    </BlockStack>
                </Card>

                {/* Features Card */}
                <Card>
                    <BlockStack gap="300">
                        <Text variant="headingMd" as="h2">Features</Text>
                        <List type="bullet">
                            <List.Item>Automatic heading detection from your content</List.Item>
                            <List.Item>Customizable styling with multiple themes</List.Item>
                            <List.Item>Smooth scroll navigation for better UX</List.Item>
                            <List.Item>Works seamlessly with pages and blog posts</List.Item>
                            <List.Item>Flexible positioning (before or after content)</List.Item>
                        </List>
                    </BlockStack>
                </Card>

                {/* Help & Support Card */}
                <Card>
                    <BlockStack gap="300">
                        <Text variant="headingMd" as="h2">Need Help?</Text>
                        <Text variant="bodyMd" as="p">
                            Get the most out of Easy Table Of Contents with our documentation and support resources.
                        </Text>
                        <ButtonGroup>
                            <Button url="#" external>Documentation</Button>
                            <Button url="#" external>Contact Support</Button>
                        </ButtonGroup>
                    </BlockStack>
                </Card>

                {/* Technical Info Card */}
                <Card>
                    <BlockStack gap="300">
                        <Text variant="headingMd" as="h2">Technical Information</Text>
                        <Text variant="bodyMd" as="p">
                            Built for Shopify compliance with Polaris design system components.
                        </Text>
                        <List type="bullet">
                            <List.Item>Built for Shopify approved design patterns</List.Item>
                            <List.Item>Responsive and mobile-friendly interface</List.Item>
                            <List.Item>No external embedded content for security</List.Item>
                            <List.Item>Seamless Shopify App Bridge integration</List.Item>
                        </List>
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
};

export default About;
