import React from 'react';
import {
    Page,
    InlineGrid,
    BlockStack,
    Card,
} from "@shopify/polaris";
import { ArrowLeftIcon } from '@shopify/polaris-icons';
import { router } from '@inertiajs/react';
import Sidebar from "../components/sidebar";
import { Pricing } from "../components/Pricing";

const PricingPage = () => {
    const backToDashboard = () => {
        router.visit('/home');
    };

    return (
        <Page 
            title="Pricing Plans" 
            fullWidth
            backAction={{ content: "Back", onAction: backToDashboard }}
        >
            {/* Two-column layout: Sidebar and Pricing */}
            <InlineGrid columns={{ xs: 1, md: "0.5fr 3.5fr" }} gap="400">
                {/* Sidebar takes 1/4th of the screen width */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar />
                    </Card>
                </BlockStack>

                {/* Pricing content takes 3/4th of the screen width */}
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
