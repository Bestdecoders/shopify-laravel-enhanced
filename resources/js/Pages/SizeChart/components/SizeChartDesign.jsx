import { 
    Card, 
    BlockStack, 
    Box, 
    Text, 
    Button,
    Banner,
    Divider,
    Icon,
    InlineStack
} from "@shopify/polaris";
import { ColorIcon } from "@shopify/polaris-icons";
import { useCallback } from "react";
import { router } from "@inertiajs/react";
import AdvancedCSS from "../../../components/AdvancedCSS";

const SizeChartDesign = ({ data, onChange, isPaidUser = true }) => {
    const handleCustomStyleChange = useCallback((customStyle) => {
        onChange({
            globalStyle: {
                ...data.globalStyle,
                customStyle
            }
        });
    }, [data.globalStyle, onChange]);

    const goToGlobalStyles = useCallback(() => {
        router.visit('/globalStyle');
    }, []);

    return (
        <Card>
            <Box padding="500">
                <Box paddingBlockEnd="400">
                    <InlineStack align="space-between">
                        <Box>
                            <Text variant="headingMd" as="h2">
                                Design & Styling
                            </Text>
                            <Text variant="bodyMd" color="subdued">
                                Customize the visual appearance of your size chart
                            </Text>
                        </Box>
                        <Icon source={ColorIcon} color="subdued" />
                    </InlineStack>
                </Box>
                <Divider />
                <Box paddingBlockStart="500">
                    <BlockStack gap="500">
                        <Banner
                            title="Global Style Settings"
                            status="info"
                            action={{
                                content: "Manage Global Styles",
                                onAction: goToGlobalStyles
                            }}
                        >
                            <Text variant="bodyMd">
                                Colors, fonts, and button styles are managed globally across all size charts. 
                                Use the Global Style editor to maintain consistent branding.
                            </Text>
                        </Banner>

                        {isPaidUser ? (
                            <Card>
                                <Box padding="400">
                                    <Box paddingBlockEnd="300">
                                        <Text variant="headingSm" as="h3">
                                            Custom CSS (Advanced)
                                        </Text>
                                        <Text variant="bodyMd" color="subdued">
                                            Add custom CSS to override default styles for this specific size chart
                                        </Text>
                                    </Box>
                                    <Divider />
                                    <Box paddingBlockStart="400">
                                        <AdvancedCSS
                                            isPaidUser={isPaidUser}
                                            cssCode={data.globalStyle?.customStyle || ""}
                                            handleCSSChange={handleCustomStyleChange}
                                        />
                                    </Box>
                                </Box>
                            </Card>
                        ) : (
                            <Banner
                                title="Unlock Advanced Styling"
                                status="warning"
                                action={{
                                    content: "Upgrade Plan"
                                }}
                            >
                                <Text variant="bodyMd">
                                    Get access to custom CSS, advanced color options, and premium design features with our paid plans.
                                </Text>
                            </Banner>
                        )}
                    </BlockStack>
                </Box>
            </Box>
        </Card>
    );
};

export default SizeChartDesign;