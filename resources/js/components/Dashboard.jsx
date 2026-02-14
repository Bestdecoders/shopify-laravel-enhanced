import {
    Card,
    Box,
    Button,
} from "@shopify/polaris";
import Editor from "./SCEditor";
import TableEditor from "./TableEditor";
import { useAxios } from "../hooks/useAxios";
import { useEffect } from "react";
const Dashboard = () => {
    const data = [
        { name: "Jan", salesA: 1000, salesB: 800, salesC: 1200 },
        { name: "Feb", salesA: 500, salesB: 1500, salesC: 1800 },
        { name: "Mar", salesA: 1500, salesB: 1200, salesC: 1600 },
        // Add more data points as needed
    ];
    const axios = useAxios();

    // Auto-check billing on mount
    useEffect(() => {
        checkBilling();
        console.log('changed');
        
    }, []);

    const checkBilling =  async () => {
       await axios.get("/redirect-pricing").then((response) => {
            console.log(response);
        });
    }

    return (
        <>
            <Card sectioned>
                <Box background="bg-primary" padding="5">
                    <Button primary onClick={checkBilling} >Button</Button>
                </Box>
            </Card>
            <Card sectioned>
                <Box background="bg-primary" padding="5">
                    <Editor />
                </Box>
            </Card>
            <Card sectioned>
                <Box padding="0">
                    <TableEditor />
                </Box>
            </Card>
        </>
    );
};

export default Dashboard;
