import React from "react";
import Navbar from "@/Components/Navbar";
import Sidebar from "../components/sidebar";
import { Page, InlineGrid, BlockStack, Card, Box, Divider } from '@shopify/polaris';
import { useNavigateWithToken } from "../hooks/useNavigateWithToken"; // Import your helper

const About = () => {
    // const navigateWithHostAndToken = useNavigateWithToken(); // Initialize the navigation hook

    // const navigateToDashboard = () => {
    //     navigateWithHostAndToken("/home"); // Dynamically navigate to dashboard
    // };

    return (
        <Page title="Settings" fullWidth>
            {/* Two-column layout: Sidebar and Dashboard */}
            <InlineGrid columns={{ xs: 1, md: "0.5fr 3.5fr" }} gap="400">
                {/* Sidebar takes 1/4th of the screen width */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar currentPath="/about" />
                    </Card>
                </BlockStack>

                {/* Dashboard takes 3/4th of the screen width */}
                <BlockStack gap="400">
                    <Card>
                        <Box>
                            <p>
                                This is the About page.
                            </p>
                        </Box>
                    </Card>
                </BlockStack>
            </InlineGrid>
        </Page>
    );
};

export default About;
