import { 
    Card, 
    BlockStack, 
    Box, 
    Text, 
    Checkbox,
    TextField,
    Tabs,
    Divider,
    Icon,
    InlineStack,
    Badge
} from "@shopify/polaris";
import { TabletIcon, EditIcon } from "@shopify/polaris-icons";
import { useState, useCallback } from "react";
import Editor from "../../../components/Editor";
import TableEditor from "../../../components/TableEditor";

const SizeChartContent = ({ data, onChange, isPaidUser = true }) => {
    const [selectedTab, setSelectedTab] = useState(0);

    const handleTabFeatureChange = useCallback((value) => {
        onChange({
            tabs: {
                ...data.tabs,
                tab: value
            }
        });
    }, [data.tabs, onChange]);

    const handleFirstTabNameChange = useCallback((value) => {
        onChange({
            tabs: {
                ...data.tabs,
                firstTabName: value
            }
        });
    }, [data.tabs, onChange]);

    const handleSecondTabNameChange = useCallback((value) => {
        onChange({
            tabs: {
                ...data.tabs,
                secondTabName: value
            }
        });
    }, [data.tabs, onChange]);

    const handleFirstPageChange = useCallback((content) => {
        onChange({ firstPage: content });
    }, [onChange]);

    const handleSecondPageChange = useCallback((content) => {
        onChange({ secondPage: content });
    }, [onChange]);

    const handleTableDataChange = useCallback((tableData) => {
        onChange({ tableData });
    }, [onChange]);

    const tabs = [
        {
            id: "content",
            content: "Content & Table",
            panelID: "content-panel"
        },
        {
            id: "tabs-settings",
            content: "Tab Settings",
            panelID: "tabs-panel"
        }
    ];

    return (
        <Card>
            <Box padding="500">
                <Box paddingBlockEnd="400">
                    <InlineStack align="space-between">
                        <Box>
                            <Text variant="headingMd" as="h2">
                                Content Management
                            </Text>
                            <Text variant="bodyMd" color="subdued">
                                Create and organize your size chart content and layout
                            </Text>
                        </Box>
                        <Icon source={EditIcon} color="subdued" />
                    </InlineStack>
                </Box>
                <Divider />
            </Box>

            <Tabs tabs={tabs} selected={selectedTab} onSelect={setSelectedTab}>
                {selectedTab === 0 && (
                    <Box padding="500">
                        <BlockStack gap="600">
                            {/* First Page Content */}
                            <Card>
                                <Box padding="400">
                                    <Box paddingBlockEnd="300">
                                        <InlineStack align="space-between">
                                            <Box>
                                                <Text variant="headingSm" as="h3">
                                                    Introduction Content
                                                </Text>
                                                <Text variant="bodyMd" color="subdued">
                                                    Add introductory text or instructions for customers
                                                </Text>
                                            </Box>
                                            <Badge>Optional</Badge>
                                        </InlineStack>
                                    </Box>
                                    <Divider />
                                    <Box paddingBlockStart="400">
                                        <Editor
                                            content={data.firstPage || ""}
                                            onUpdate={handleFirstPageChange}
                                        />
                                    </Box>
                                </Box>
                            </Card>

                            {/* Size Chart Table */}
                            <Card>
                                <Box padding="400">
                                    <Box paddingBlockEnd="300">
                                        <InlineStack align="space-between">
                                            <Box>
                                                <Text variant="headingSm" as="h3">
                                                    Size Chart Table
                                                </Text>
                                                <Text variant="bodyMd" color="subdued">
                                                    Create your interactive size chart with measurements
                                                </Text>
                                            </Box>
                                            <InlineStack gap="200">
                                                <Badge status="attention">Required</Badge>
                                                <Icon source={TabletIcon} color="subdued" />
                                            </InlineStack>
                                        </InlineStack>
                                    </Box>
                                    <Divider />
                                    <Box paddingBlockStart="400">
                                        <TableEditor
                                            initialTableData={data.tableData || data.table || {}}
                                            onTableDataUpdate={handleTableDataChange}
                                            isPaidUser={isPaidUser}
                                        />
                                    </Box>
                                </Box>
                            </Card>

                            {/* Second Page Content */}
                            <Card>
                                <Box padding="400">
                                    <Box paddingBlockEnd="300">
                                        <InlineStack align="space-between">
                                            <Box>
                                                <Text variant="headingSm" as="h3">
                                                    Additional Information
                                                </Text>
                                                <Text variant="bodyMd" color="subdued">
                                                    Add care instructions, fit notes, or measurement tips
                                                </Text>
                                            </Box>
                                            <Badge>Optional</Badge>
                                        </InlineStack>
                                    </Box>
                                    <Divider />
                                    <Box paddingBlockStart="400">
                                        <Editor
                                            content={data.secondPage || ""}
                                            onUpdate={handleSecondPageChange}
                                        />
                                    </Box>
                                </Box>
                            </Card>
                        </BlockStack>
                    </Box>
                )}

                {selectedTab === 1 && (
                    <Box padding="500">
                        <BlockStack gap="500">
                            <Box>
                                <Text variant="headingSm" as="h3">
                                    Tab Navigation Settings
                                </Text>
                                <Text variant="bodyMd" color="subdued">
                                    Configure how customers navigate through your size chart content
                                </Text>
                            </Box>
                            
                            <Card>
                                <Box padding="400">
                                    <BlockStack gap="400">
                                        <Checkbox
                                            label="Enable tab navigation"
                                            checked={data.tabs?.tab || false}
                                            onChange={handleTabFeatureChange}
                                            helpText="Allow customers to switch between size chart and additional information"
                                        />

                                        {data.tabs?.tab && (
                                            <>
                                                <Divider />
                                                <BlockStack gap="300">
                                                    <Text variant="headingXs" as="h4">
                                                        Tab Labels
                                                    </Text>
                                                    <TextField
                                                        label="First Tab Name"
                                                        value={data.tabs?.firstTabName || "Size Chart"}
                                                        onChange={handleFirstTabNameChange}
                                                        placeholder="Size Chart"
                                                        helpText="Label for the main size chart tab"
                                                    />

                                                    <TextField
                                                        label="Second Tab Name"
                                                        value={data.tabs?.secondTabName || "Measurement Guide"}
                                                        onChange={handleSecondTabNameChange}
                                                        placeholder="Measurement Guide"
                                                        helpText="Label for the additional information tab"
                                                    />
                                                </BlockStack>
                                            </>
                                        )}
                                    </BlockStack>
                                </Box>
                            </Card>
                        </BlockStack>
                    </Box>
                )}
            </Tabs>
        </Card>
    );
};

export default SizeChartContent;