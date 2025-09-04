import Sidebar from "../components/sidebar";
import Dashboard from "../components/Dashboard";
import Navbar from "@/Components/Navbar";
import { Page, InlineGrid, BlockStack, Card, Box, Divider } from '@shopify/polaris';

const Home = () => {
    return (
        <Page title="Dashboard" fullWidth>
            {/* Two-column layout: Sidebar and Dashboard */}
            <InlineGrid columns={{ xs: 1, md: '0.5fr 3.5fr' }} gap="400">
                {/* Sidebar takes 1/4th of the screen width */}
                <BlockStack gap="400" >
                    <Card sectioned  >
                        <Sidebar />
                    </Card>
                </BlockStack>

                {/* Dashboard takes 3/4th of the screen width */}
                <BlockStack gap="400" >
                    <Card>
                        <Dashboard />
                    </Card>
                </BlockStack>
            </InlineGrid>
        </Page>
    );
};

export default Home;
