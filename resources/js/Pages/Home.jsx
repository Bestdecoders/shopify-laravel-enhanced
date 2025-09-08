import Sidebar from "../components/sidebar";
import Dashboard from "../components/Dashboard";
import { Page, InlineGrid, BlockStack, Card, Box } from '@shopify/polaris';

const Home = () => {
    return (
        <Page title="Dashboard" fullWidth>
            {/* Two-column layout: Sidebar and Dashboard */}
            <InlineGrid columns={{ xs: 1, lg: '300px 1fr' }} gap="400">
                {/* Sidebar - Fixed width on large screens */}
                <Box>
                    <Card>
                        <Sidebar />
                    </Card>
                </Box>

                {/* Dashboard - Takes remaining space */}
                <Box>
                    <Card>
                        <Box padding="400">
                            <Dashboard />
                        </Box>
                    </Card>
                </Box>
            </InlineGrid>
        </Page>
    );
};

export default Home;
