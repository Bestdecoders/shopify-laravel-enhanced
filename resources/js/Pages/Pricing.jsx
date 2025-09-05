import Sidebar from "../components/sidebar";
import {
    Page,
    InlineGrid,
    BlockStack,
    Card,
} from "@shopify/polaris";
import { Pricing } from "../components/Pricing";

const PricingPage = () => {
    return (
        <Page title="Pricing Plans" fullWidth>
            {/* Two-column layout: Sidebar and Content */}
            <InlineGrid columns={{ xs: 1, md: "0.5fr 3.5fr" }} gap="400">
                {/* Sidebar */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar currentPath="/pricing" />
                    </Card>
                </BlockStack>

                {/* Main Content */}
                <BlockStack gap="400">
                    <Card>
                        <Pricing />
                    </Card>
                </BlockStack>
            </InlineGrid>
        </Page>
    );
};

export default PricingPage;
