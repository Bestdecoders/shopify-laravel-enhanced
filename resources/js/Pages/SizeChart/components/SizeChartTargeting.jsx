import { 
    Card, 
    ChoiceList, 
    BlockStack, 
    Box, 
    Text,
    Divider,
    Icon,
    InlineStack,
    Badge
} from "@shopify/polaris";
import { TargetIcon } from "@shopify/polaris-icons";
import { useCallback } from "react";
import IterateItems from "../../../components/IterateItems";

const SizeChartTargeting = ({ data, onChange }) => {
    const targetTypeOptions = [
        { label: "Products", value: "product" },
        { label: "Collections", value: "collection" }
    ];

    const handleTargetTypeChange = useCallback((value) => {
        onChange({ 
            targetType: value,
            targets: {} // Reset targets when type changes
        });
    }, [onChange]);

    const handleTargetsChange = useCallback((value) => {
        onChange({ targets: value });
    }, [onChange]);

    return (
        <Card>
            <Box padding="500">
                <Box paddingBlockEnd="400">
                    <InlineStack align="space-between">
                        <Box>
                            <Text variant="headingMd" as="h2">
                                Target Assignment
                            </Text>
                            <Text variant="bodyMd" color="subdued">
                                Choose which products or collections will display this size chart
                            </Text>
                        </Box>
                        <Icon source={TargetIcon} color="subdued" />
                    </InlineStack>
                </Box>
                <Divider />
                <Box paddingBlockStart="500">
                    <BlockStack gap="500">
                        <Box>
                            <ChoiceList
                                title="Target Type"
                                choices={targetTypeOptions}
                                selected={data.targetType || ["product"]}
                                onChange={handleTargetTypeChange}
                            />
                        </Box>

                        <Card>
                            <Box padding="400">
                                <Box paddingBlockEnd="300">
                                    <InlineStack align="space-between">
                                        <Box>
                                            <Text variant="headingSm" as="h3">
                                                Select {data.targetType?.[0] === "product" ? "Products" : "Collections"}
                                            </Text>
                                            <Text variant="bodyMd" color="subdued">
                                                Choose specific {data.targetType?.[0] === "product" ? "products" : "collections"} to show this size chart
                                            </Text>
                                        </Box>
                                        <Badge status="attention">Required</Badge>
                                    </InlineStack>
                                </Box>
                                <Divider />
                                <Box paddingBlockStart="400">
                                    <IterateItems
                                        type={data.targetType || ["product"]}
                                        selectedItems={data.targets || {}}
                                        setSelectedItems={handleTargetsChange}
                                    />
                                </Box>
                            </Box>
                        </Card>
                    </BlockStack>
                </Box>
            </Box>
        </Card>
    );
};

export default SizeChartTargeting;