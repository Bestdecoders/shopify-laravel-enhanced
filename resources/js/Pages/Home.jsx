import Dashboard from "../components/Dashboard";
import { Page, InlineGrid, BlockStack, Card, Box, Divider } from '@shopify/polaris';

const Home = () => {
    return (
        <Page title="Dashboard" >
            <BlockStack gap="400" >
                <Card>
                    <Dashboard />
                </Card>
            </BlockStack>
        </Page>
    );
};

export default Home;
