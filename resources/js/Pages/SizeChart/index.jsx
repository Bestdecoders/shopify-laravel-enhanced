import { useEffect, useState } from "react";
import { Page, Box, BlockStack, Card, Badge, Text, Divider, Button, InlineGrid, Toast, Frame } from "@shopify/polaris";
import { XSmallIcon, ExportIcon } from "@shopify/polaris-icons";
import { router } from "@inertiajs/react";
import { useAxios } from "../../hooks/useAxios";
import { useSizeChartForm } from "./hooks/useSizeChartForm";
import { useSizeChartActions } from "./hooks/useSizeChartActions";
import Sidebar from "../../components/sidebar";

// Organized components
import SizeChartMetadata from "./components/SizeChartMetadata";
import SizeChartContent from "./components/SizeChartContent";
import SizeChartDesign from "./components/SizeChartDesign";
import SizeChartTargeting from "./components/SizeChartTargeting";
import SizeChartPreview from "../../components/SizeChartPreview";

const SizeChart = ({ id, isPaidUser = true }) => {
    const axios = useAxios();
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const [isClosing, setIsClosing] = useState(false);
    const [isExporting, setIsExporting] = useState(false);
    const [toastActive, setToastActive] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [toastError, setToastError] = useState(false);
    const {
        formData,
        updateFormData,
        setFormData: setInitialData,
        isValid
    } = useSizeChartForm(id);
    
    const {
        save,
        saveAndExit,
        deleteSizeChart,
        isSaving
    } = useSizeChartActions(formData, id);

    const handleClosePreview = () => {
        setIsClosing(true);
        setTimeout(() => {
            setIsPreviewOpen(false);
            setIsClosing(false);
        }, 300); // Match animation duration
    };

    // Load existing size chart data
    useEffect(() => {
        if (id) {
            // Use the legacy endpoint that actually works
            axios.post(`/get/size-chart/${id}`)
                .then((response) => {
                    const { sizeChart, globalStyle } = response.data;
                    
                    
                    // Prepare the complete data object for the form
                    const completeData = {
                        // Basic metadata
                        name: sizeChart.name,
                        status: sizeChart.status,
                        appearance: sizeChart.appearance,
                        country: sizeChart.country,
                        
                        // Targeting data
                        targetType: sizeChart.targetType,
                        target: sizeChart.target,
                        
                        // Content data
                        firstPage: sizeChart.firstPage,
                        secondPage: sizeChart.secondPage,
                        tableData: sizeChart.table,
                        table: sizeChart.table, // Keep both for backward compatibility
                        tabs: sizeChart.tabs,
                        
                        // Global style
                        globalStyle: globalStyle
                    };
                    
                    
                    // Use the hook's setInitialData which handles all the transformation
                    setInitialData(completeData);
                })
                .catch((error) => {
                    console.error("Failed to load size chart:", error);
                });
        } else {
            // Load global settings for new size charts
            axios.post('/get/global-settings')
                .then((response) => {
                    const globalStyle = response.data?.globalStyle 
                        ? JSON.parse(response.data.globalStyle) 
                        : {};
                    setInitialData({ globalStyle });
                })
                .catch(console.error);
        }
    }, [id, setInitialData]);

    const goBack = () => router.visit('/sizecharts');

    // Export function for single size chart
    const handleExport = async (format = 'xlsx') => {
        if (!id) return; // Only export if editing existing size chart
        
        setIsExporting(true);
        try {
            const response = await axios.post(`/export/size-charts`, {
                format: format,
                ids: [id] // Export only this size chart
            }, {
                responseType: 'blob',
            });

            // Create download link
            const blob = new Blob([response.data]);
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `size-chart-${formData.name || id}-${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            setToastMessage(`Size chart exported successfully as ${format.toUpperCase()}`);
            setToastError(false);
            setToastActive(true);
            
        } catch (error) {
            console.error('Export error:', error);
            setToastMessage(error.response?.data?.message || 'Failed to export size chart');
            setToastError(true);
            setToastActive(true);
        } finally {
            setIsExporting(false);
        }
    };

    const toggleToast = () => setToastActive((active) => !active);

    const toastMarkup = toastActive ? (
        <Toast
            content={toastMessage}
            error={toastError}
            onDismiss={toggleToast}
            duration={4000}
        />
    ) : null;

    return (
        <Frame>
            {toastMarkup}
            <Page
            primaryAction={{
                content: isSaving ? "Saving..." : "Save & Exit",
                onClick: saveAndExit,
                disabled: !isValid || isSaving,
                loading: isSaving
            }}
            secondaryActions={[
                { 
                    content: "Save Draft", 
                    onClick: save,
                    disabled: !isValid || isSaving
                },
                ...(id ? [
                    {
                        content: "Export Excel",
                        icon: ExportIcon,
                        onClick: () => handleExport('xlsx'),
                        disabled: isSaving || isExporting,
                        loading: isExporting
                    }
                ] : []),
                { 
                    content: "Delete", 
                    onClick: deleteSizeChart,
                    disabled: !id || isSaving,
                    destructive: true
                }
            ]}
            backAction={{ content: "Size Charts", onAction: goBack }}
            titleMetadata={id && <Badge status="info">ID: {id}</Badge>}
            subtitle={id ? "Modify your existing size chart configuration" : "Create a new size chart for your products"}
            title={
                <Box>
                    <Text variant="headingLg" as="h1">
                        {id ? `Edit Size Chart${formData.name ? `: ${formData.name}` : ''}` : "Create New Size Chart"}
                    </Text>
                </Box>
            }
            fullWidth
        >
            {/* Two-column layout: Sidebar and Size Chart Content */}
            <InlineGrid columns={{ xs: 1, md: '0.5fr 3.5fr' }} gap="400">
                {/* Sidebar takes 1/4th of the screen width */}
                <BlockStack gap="400">
                    <Card sectioned>
                        <Sidebar />
                    </Card>
                </BlockStack>

                {/* Size Chart Content takes 3/4th of the screen width */}
                <BlockStack gap="600">
                    {/* Metadata Section */}
                    <SizeChartMetadata
                        data={formData}
                        onChange={updateFormData}
                        isPaidUser={isPaidUser}
                    />

                    {/* Content Section */}
                    <SizeChartContent
                        data={formData}
                        onChange={updateFormData}
                        isPaidUser={isPaidUser}
                    />

                    {/* Targeting Section */}
                    <SizeChartTargeting
                        data={formData}
                        onChange={updateFormData}
                    />

                    {/* Design Section */}
                    <SizeChartDesign
                        data={formData}
                        onChange={updateFormData}
                        isPaidUser={isPaidUser}
                    />
                </BlockStack>
            </InlineGrid>

            {/* Floating Preview Button - Vertical */}
            <div style={{
                position: 'fixed',
                right: '0px',
                top: '50%',
                transform: 'translateY(-50%)',
                zIndex: 1000
            }}>
                <div
                    className="vertical-preview-button"
                    onClick={() => setIsPreviewOpen(true)}
                    style={{
                        position: 'relative',
                        cursor: 'pointer',
                        backgroundColor: '#3b82f6',
                        color: 'white',
                        border: 'none'
                    }}
                >
                    <div style={{ 
                        transform: 'rotate(0deg)',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px'
                    }}>
                        <svg 
                            width="16" 
                            height="16" 
                            viewBox="0 0 20 20" 
                            fill="currentColor"
                            style={{ marginBottom: '4px' }}
                        >
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 3.314-2.686 6-6 6s-6-2.686-6-6a5.98 5.98 0 01.332-1.973z" clipRule="evenodd" />
                        </svg>
                        <span>PREVIEW</span>
                    </div>
                </div>
            </div>

            {/* Preview Drawer */}
            {isPreviewOpen && (
                <>
                    {/* Backdrop with fade-in/out animation */}
                    <div 
                        style={{
                            position: 'fixed',
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            backgroundColor: 'rgba(0, 0, 0, 0.5)',
                            zIndex: 1999,
                            animation: isClosing ? 'fadeOut 0.3s ease-out' : 'fadeIn 0.3s ease-out'
                        }}
                        onClick={handleClosePreview}
                    />
                    
                    {/* Drawer - Larger and with slide animation */}
                    <div 
                        className={`preview-drawer ${isClosing ? 'closing' : ''}`}
                        style={{
                            position: 'fixed',
                            top: 0,
                            right: 0,
                            bottom: 0,
                            width: window.innerWidth <= 768 ? '100%' : '650px',
                            backgroundColor: 'white',
                            boxShadow: '-2px 0 20px rgba(0, 0, 0, 0.15)',
                            zIndex: 2000,
                            overflow: 'auto',
                            animation: isClosing 
                                ? 'slideOutToRight 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19)' 
                                : 'slideInFromRight 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                        }}
                    >
                        {/* Hanging Close Button */}
                        <div style={{
                            position: 'absolute',
                            left: '-20px',
                            top: '50%',
                            transform: 'translateY(-50%)',
                            zIndex: 2001
                        }}>
                            <Button
                                variant="primary"
                                tone="critical"
                                size="large"
                                onClick={handleClosePreview}
                                icon={XSmallIcon}
                                accessibilityLabel="Close preview"
                                style={{
                                    borderRadius: '50%',
                                    width: '40px',
                                    height: '40px',
                                    padding: 0,
                                    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.2)'
                                }}
                            />
                        </div>

                        <Card>
                            <Box padding="400">
                                <Box paddingBlockEnd="300">
                                    <Box>
                                        <Text variant="headingLg" as="h2">
                                            Live Preview
                                        </Text>
                                        <Text variant="bodyMd" color="subdued">
                                            See how your size chart will appear to customers
                                        </Text>
                                    </Box>
                                </Box>
                                <Divider />
                                <Box paddingBlockStart="300">
                                    <SizeChartPreview chartData={{
                                        ...formData,
                                        table: formData.tableData && Object.keys(formData.tableData).length > 0 ? {
                                            ...formData.tableData,
                                            tableContent: formData.tableData.tableContent || []
                                        } : {
                                            mode: "cm",
                                            includeHeader: true,
                                            Converter: "yes",
                                            isSticky: false,
                                            tableContent: []
                                        }
                                    }} />
                                </Box>
                            </Box>
                        </Card>
                    </div>
                </>
            )}
        </Page>
        </Frame>
    );
};

export default SizeChart;