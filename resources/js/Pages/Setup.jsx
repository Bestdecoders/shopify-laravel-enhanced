import React, { useState, useEffect } from "react";
import { useNavigateWithToken } from "../hooks/useNavigateWithToken";
import { useAxios } from "../hooks/useAxios"; // Import the custom axios hook
import { Page, Button } from "@shopify/polaris";

const Setup = () => {
    const [currentStep, setCurrentStep] = useState(1); // Track the current step
    const [setupCompleted, setSetupCompleted] = useState(false); // Track setup completion
    const [isFetching, setIsFetching] = useState(true); // Loading state for initial DB fetch
    const navigateWithHostAndToken  = useNavigateWithToken(); // Get host from the custom hook
    const axios = useAxios(); // Use the axios hook for API requests

    // Fetch setup status from DB on component mount
    useEffect(() => {
        const fetchSetupStatus = async () => {
            try {
                const response = await axios.get("/setup-status"); // Axios automatically includes host
                const { setupComplete } = response.data;
                if (!setupComplete) {
                    console.log(setupComplete);
                    // If setup is already completed, redirect to dashboard
                    navigateWithHostAndToken("/home");
                } else {
                    // If setup is not complete, load the step from localStorage
                    const savedStep = localStorage.getItem("setupStep");
                    if (savedStep) {
                        setCurrentStep(Number(savedStep)); // Restore step from localStorage
                    }
                }
            } catch (error) {
                console.error("Error fetching setup status from DB:", error);
            } finally {
                setIsFetching(false); // Stop loading once the request is complete
            }
        };

        fetchSetupStatus();
    }, []);

    // Function to handle moving to the next setup step
const goToNextStep = () => {
        const nextStep = currentStep + 1;
        setCurrentStep(nextStep);
        localStorage.setItem("setupStep", nextStep); // Save the current step in localStorage
    };

    // Complete setup and update the status in the database
    const completeSetup = async () => {
        try {
            await axios.post("/api/complete-setup"); // Make an API call to mark setup as completed
            localStorage.removeItem("setupStep"); // Clear localStorage setup progress
            setSetupCompleted(true); // Mark setup as completed
        } catch (error) {
            console.error("Error completing setup:", error);
        }
    };

    // Redirect to the dashboard when setup is complete
    useEffect(() => {
        if (setupCompleted) {
            navigateWithHostAndToken("/dashboard"); // Redirect to dashboard
        }
    }, [setupCompleted, navigateWithHostAndToken]);

    if (isFetching) {
        return <div>Loading setup status...</div>; // Show loading state during data fetch
    }

    return (
        <Page title="Setup Process">
            <p>Step {currentStep}: Complete the setup step</p>

            {currentStep < 3 ? (
                <Button onClick={goToNextStep}>Next Step</Button>
            ) : (
                <Button primary onClick={completeSetup}>
                    Complete Setup
                </Button>
            )}
        </Page>
    );
};

export default Setup;
