import { useState, useCallback } from "react";
import { router } from "@inertiajs/react";
import { useAxios } from "../../../hooks/useAxios";

export const useSizeChartActions = (formData, id) => {
    const [isLoading, setIsLoading] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const axios = useAxios();

    const save = useCallback(async () => {
        if (isSaving) return;
        
        setIsSaving(true);
        const saveUrl = id ? `/save/size-chart/${id}` : `/save/size-chart`;
        
        // Transform form data to match API expectations
        
        const apiData = {
            // Basic metadata
            name: formData.name || "",
            status: formData.status || ["draft"],
            appearance: formData.appearance || ["popup"],
            country: formData.country || null,
            
            // Targeting data (form uses 'targets', API expects 'target')
            targetType: formData.targetType || ["product"],
            target: formData.targets || {},
            
            // Content data (form uses 'tableData', API expects 'table')
            firstPage: formData.firstPage || "",
            secondPage: formData.secondPage || "",
            table: formData.tableData || {
                mode: "cm",
                includeHeader: true,
                Converter: "yes",
                isSticky: false,
                tableContent: []
            },
            
            // Tab configuration
            tabs: formData.tabs || {
                tab: false,
                firstTabName: "Size Chart",
                secondTabName: "Measurement Guide"
            },
            
            // Global styling
            style: formData.globalStyle || {}
        };

        
        try {
            const response = await axios.post(saveUrl, apiData);
            
            // If creating new, redirect to edit page
            if (!id && response.data?.id) {
                router.visit(`/sizechart/${response.data.id}`);
            }
            
            return response.data;
        } catch (error) {
            console.error("Error saving size chart:", error);
            throw error;
        } finally {
            setIsSaving(false);
        }
    }, [formData, id, isSaving, axios]);

    const saveAndExit = useCallback(async () => {
        try {
            await save();
            router.visit('/sizecharts');
        } catch (error) {
            console.error("Error saving and exiting:", error);
        }
    }, [save]);

    const deleteSizeChart = useCallback(async () => {
        if (!id || isLoading) return;
        
        const confirmed = window.confirm(
            "Are you sure you want to delete this size chart? This action cannot be undone."
        );
        
        if (!confirmed) return;

        setIsLoading(true);
        try {
            // Use the legacy delete endpoint that works
            await axios.post('/delete/size-chart', { id });
            router.visit('/sizecharts');
        } catch (error) {
            console.error("Error deleting size chart:", error);
            alert("Error deleting size chart. Please try again.");
        } finally {
            setIsLoading(false);
        }
    }, [id, isLoading, axios]);

    const duplicate = useCallback(async () => {
        if (!id || isLoading) return;
        
        setIsLoading(true);
        try {
            const response = await axios.post(`/duplicate/size-chart/${id}`);
            router.visit(`/sizechart/${response.data.id}`);
        } catch (error) {
            console.error("Error duplicating size chart:", error);
            alert("Error duplicating size chart. Please try again.");
        } finally {
            setIsLoading(false);
        }
    }, [id, isLoading, axios]);

    return {
        save,
        saveAndExit,
        deleteSizeChart,
        duplicate,
        isLoading,
        isSaving
    };
};