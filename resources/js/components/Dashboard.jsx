import {
    CalloutCard,
    Grid,
    Icon,
    Page,
    Text,
    Card,
    Box,
} from "@shopify/polaris";
import { SmileyHappyIcon } from "@shopify/polaris-icons";
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
} from "recharts";
import Editor from "./SCEditor";
import TableEditor from "./TableEditor";

const Dashboard = () => {
    const data = [
        { name: "Jan", salesA: 1000, salesB: 800, salesC: 1200 },
        { name: "Feb", salesA: 500, salesB: 1500, salesC: 1800 },
        { name: "Mar", salesA: 1500, salesB: 1200, salesC: 1600 },
        // Add more data points as needed
    ];

    return (
        <>
            <Card sectioned>
                <Box background="bg-primary" padding="5">
                    <Editor />
                </Box>
            </Card>
            <Card sectioned>
                <Box padding="0" fullWidth>
                    <TableEditor />
                </Box>
            </Card>
            <Card sectioned>
                <Box background="bg-primary" padding="5">
                    <Editor />
                </Box>
            </Card>
        </>
    );
};

export default Dashboard;
