import { Page, InlineGrid, BlockStack, Card } from "@shopify/polaris";
import Sidebar from "./sidebar";

export default function AppLayout({ 
    title, 
    currentPath, 
    children, 
    fullWidth = true,
    showSidebar = true
}) {
    if (!showSidebar) {
        return (
            <Page title={title} fullWidth={fullWidth}>
                {children}
            </Page>
        );
    }

    return (
        <Page title={title} fullWidth={fullWidth}>
            <InlineGrid columns={{ xs: 1, md: '0.5fr 3.5fr' }} gap="400">
                {/* Sidebar */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar currentPath={currentPath} />
                    </Card>
                </BlockStack>

                {/* Main Content */}
                <BlockStack gap="400">
                    {children}
                </BlockStack>
            </InlineGrid>
        </Page>
    );
}