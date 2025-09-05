import DataManager from "./DataManager";

// =============================================================================
// ⚠️  SIZE CHARTS - Specialized implementation using generalized DataManager
// =============================================================================
// This component demonstrates how to use the DataManager for specific use cases
// All the CRUD functionality is handled by the DataManager component
// =============================================================================

export default function SizeCharts() {
    // Configuration specific to Size Charts
    const sizeChartConfig = {
        // Search fields for filtering
        searchFields: ['title', 'description', 'category'],
        
        // Status field for filtering
        statusField: 'status',
        
        // Custom sort options
        sortOptions: [
            { label: 'Size Chart A-Z', value: 'title_asc' },
            { label: 'Size Chart Z-A', value: 'title_desc' },
            { label: 'Category A-Z', value: 'category_asc' },
            { label: 'Category Z-A', value: 'category_desc' },
            { label: 'Created (newest first)', value: 'created_at_desc' },
            { label: 'Created (oldest first)', value: 'created_at_asc' },
            { label: 'Updated (newest first)', value: 'updated_at_desc' },
            { label: 'Updated (oldest first)', value: 'updated_at_asc' },
        ],
        
        // Status options
        statusOptions: [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' },
            { label: 'Draft', value: 'draft' },
        ],
        
        // Custom tabs for filtering
        tabs: [
            { 
                id: 'all', 
                content: 'All Size Charts', 
                filter: () => true 
            },
            { 
                id: 'active', 
                content: 'Active', 
                filter: (item) => item.status === 'active' 
            },
            { 
                id: 'inactive', 
                content: 'Inactive', 
                filter: (item) => item.status === 'inactive' 
            },
            { 
                id: 'draft', 
                content: 'Drafts', 
                filter: (item) => item.status === 'draft' 
            },
        ],
        
        // Fields to display in the data table
        displayFields: [
            { 
                key: 'title', 
                label: 'Title', 
                primary: true 
            },
            { 
                key: 'category', 
                label: 'Category' 
            },
            { 
                key: 'status', 
                label: 'Status', 
                type: 'status' 
            },
            { 
                key: 'created_at', 
                label: 'Created', 
                type: 'date' 
            },
        ],
        
        // Form fields for create/edit
        formFields: [
            {
                key: 'title',
                type: 'text',
                label: 'Size Chart Title',
                required: true,
                placeholder: 'Enter size chart title',
                helpText: 'A descriptive name for your size chart'
            },
            {
                key: 'description',
                type: 'textarea',
                label: 'Description',
                placeholder: 'Describe this size chart...',
                helpText: 'Optional description to help identify this size chart',
                rows: 3
            },
            {
                key: 'category',
                type: 'select',
                label: 'Category',
                required: true,
                options: [
                    { label: 'Select category...', value: '' },
                    { label: 'Clothing', value: 'clothing' },
                    { label: 'Shoes', value: 'shoes' },
                    { label: 'Accessories', value: 'accessories' },
                    { label: 'Jewelry', value: 'jewelry' },
                    { label: 'Sports Equipment', value: 'sports' },
                    { label: 'Other', value: 'other' },
                ],
                helpText: 'Choose the most appropriate category'
            },
            {
                key: 'measurements',
                type: 'textarea',
                label: 'Measurements (JSON)',
                placeholder: '{"XS": {"chest": "32-34", "waist": "26-28"}, "S": {"chest": "34-36", "waist": "28-30"}}',
                helpText: 'Enter size measurements in JSON format',
                rows: 4
            },
            {
                key: 'display_units',
                type: 'select',
                label: 'Display Units',
                options: [
                    { label: 'Inches', value: 'inches' },
                    { label: 'Centimeters', value: 'cm' },
                    { label: 'Both', value: 'both' },
                ],
                defaultValue: 'inches',
                helpText: 'Units to display measurements in'
            },
            {
                key: 'products',
                type: 'products',
                label: 'Assign to Products',
                helpText: 'Select specific products to show this size chart on',
                required: false
            },
            {
                key: 'collections',
                type: 'collections',
                label: 'Assign to Collections',
                helpText: 'Select collections to automatically apply this size chart to all products in those collections',
                required: false
            },
            {
                key: 'show_fit_guide',
                type: 'checkbox',
                label: 'Show Fit Guide',
                helpText: 'Display additional fit guidance to customers'
            },
            {
                key: 'fit_guide_text',
                type: 'textarea',
                label: 'Fit Guide Text',
                placeholder: 'Enter fit guidance for customers...',
                helpText: 'Optional text to help customers choose the right size',
                rows: 2
            },
            {
                key: 'popup_trigger',
                type: 'select',
                label: 'Display Trigger',
                options: [
                    { label: 'Size Chart Link', value: 'link' },
                    { label: 'Size Guide Button', value: 'button' },
                    { label: 'Auto on Page Load', value: 'auto' },
                ],
                defaultValue: 'link',
                helpText: 'How customers will access this size chart'
            },
            {
                key: 'status',
                type: 'select',
                label: 'Status',
                options: [
                    { label: 'Draft', value: 'draft' },
                    { label: 'Active', value: 'active' },
                    { label: 'Inactive', value: 'inactive' },
                ],
                required: true,
                defaultValue: 'draft',
                helpText: 'Current status of this size chart'
            }
        ],
        
        // Image field for avatars (if size charts had images)
        imageField: 'image',
        
        // Custom validation
        validate: (data) => {
            const errors = {};
            
            // Validate JSON measurements
            if (data.measurements) {
                try {
                    JSON.parse(data.measurements);
                } catch (e) {
                    errors.measurements = 'Please enter valid JSON format for measurements';
                }
            }
            
            // Validate that either products or collections are selected if status is active
            if (data.status === 'active') {
                const hasProducts = data.products && data.products.length > 0;
                const hasCollections = data.collections && data.collections.length > 0;
                
                if (!hasProducts && !hasCollections) {
                    errors._general = 'Active size charts must be assigned to at least one product or collection';
                }
            }
            
            return errors;
        }
    };
    
    return (
        <DataManager
            entityType="sizecharts"
            entityLabel="Size Charts"
            entitySingular="Size Chart"
            apiEndpoint="/api/data/sizecharts"
            config={sizeChartConfig}
        />
    );
}
