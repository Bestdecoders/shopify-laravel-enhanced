import { useState, useCallback, useMemo } from "react";
import { convertTableContent } from "../../../hooks/helper";

const initialFormData = {
    name: "",
    status: ["draft"],
    appearance: ["popup"],
    country: [],
    targetType: ["product"],
    targets: {},
    firstPage: "",
    secondPage: "",
    tableData: {
        mode: "cm",
        includeHeader: true,
        Converter: "yes",
        isSticky: false,
        showCreateForm: true,
        tableContent: []
    },
    tabs: {
        tab: false,
        firstTabName: "Size Chart",
        secondTabName: "Measurement Guide"
    },
    globalStyle: {}
};

export const useSizeChartForm = (id) => {
    const [formData, setFormData] = useState(initialFormData);
    const [isDirty, setIsDirty] = useState(false);

    const updateFormData = useCallback((updates) => {
        setFormData(prev => {
            const newData = { ...prev, ...updates };
            setIsDirty(true);
            return newData;
        });
    }, []);

    const updateNestedData = useCallback((path, value) => {
        setFormData(prev => {
            const newData = { ...prev };
            const keys = path.split('.');
            let current = newData;
            
            // Navigate to the nested object
            for (let i = 0; i < keys.length - 1; i++) {
                if (!current[keys[i]]) {
                    current[keys[i]] = {};
                }
                current = current[keys[i]];
            }
            
            // Set the value
            current[keys[keys.length - 1]] = value;
            setIsDirty(true);
            return newData;
        });
    }, []);

    // Computed properties for validation
    const isValid = useMemo(() => {
        return (
            formData.name?.trim() &&
            formData.status?.length > 0 &&
            formData.appearance?.length > 0 &&
            formData.targetType?.length > 0
        );
    }, [formData]);

    // Computed data for saving (with processed table data)
    const processedFormData = useMemo(() => {
        return {
            ...formData,
            table: convertTableContent(formData.tableData)
        };
    }, [formData]);

    const resetForm = useCallback(() => {
        setFormData(initialFormData);
        setIsDirty(false);
    }, []);

    const setInitialData = useCallback((data) => {
        
        // Process incoming data to match our form structure
        const processedData = {
            // Basic metadata
            name: data.name || "",
            status: Array.isArray(data.status) ? data.status : [data.status || "draft"],
            appearance: Array.isArray(data.appearance) ? data.appearance : [data.appearance || "popup"],
            country: Array.isArray(data.country) ? data.country : 
                     (data.country ? 
                         (typeof data.country === 'string' ? 
                             (data.country.startsWith('[') ? JSON.parse(data.country) : [data.country]) 
                             : [data.country]) 
                         : []),
            
            // Targeting data  
            targetType: Array.isArray(data.targetType) ? data.targetType : [data.targetType || "product"],
            targets: data.target || data.targets || {},
            
            // Content data
            firstPage: data.firstPage || "",
            secondPage: data.secondPage || "",
            tableData: data.table || data.tableData || initialFormData.tableData,
            tabs: data.tabs || initialFormData.tabs,
            
            // Global style
            globalStyle: typeof data.globalStyle === 'string' 
                ? JSON.parse(data.globalStyle) 
                : (data.globalStyle || {})
        };
        
        
        setFormData(processedData);
        setIsDirty(false);
    }, []);

    return {
        formData,
        processedFormData,
        updateFormData,
        updateNestedData,
        setFormData: setInitialData,
        resetForm,
        isValid,
        isDirty
    };
};