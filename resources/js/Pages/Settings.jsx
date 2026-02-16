import React, { useState } from "react";
import {
    Page,
    BlockStack,
    Card,
    Text,
    TextField,
    Select,
    Checkbox,
    ContextualSaveBar
} from '@shopify/polaris';
import { useAxios } from "../hooks/useAxios";

const Settings = () => {
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const axios = useAxios();

    const [settings, setSettings] = useState({
        headingLevel: 'h2',
        position: 'before',
        autoShow: true,
        theme: 'light',
        smoothScroll: true,
    });

    const handleChange = (field, value) => {
        setSettings(prev => ({
            ...prev,
            [field]: value
        }));
        setIsDirty(true);
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            // TODO: Save to your API endpoint
            // await axios.post("/api/settings", settings);
            console.log('Settings saved:', settings);
            setIsDirty(false);
        } catch (error) {
            console.error("Error saving settings:", error);
        } finally {
            setSaving(false);
        }
    };

    const handleDiscard = () => {
        // Reset to default values
        setSettings({
            headingLevel: 'h2',
            position: 'before',
            autoShow: true,
            theme: 'light',
            smoothScroll: true,
        });
        setIsDirty(false);
    };

    return (
        <>
            {isDirty && (
                <ContextualSaveBar
                    message="Unsaved changes"
                    saveAction={{
                        onAction: handleSave,
                        loading: saving,
                        disabled: saving
                    }}
                    discardAction={{
                        onAction: handleDiscard,
                        disabled: saving
                    }}
                />
            )}

            <Page title="Settings">
                <BlockStack gap="400">
                    {/* General Settings Section */}
                    <Card>
                        <BlockStack gap="400">
                            <Text variant="headingMd" as="h2">General Settings</Text>

                            <TextField
                                label="Default heading level"
                                type="text"
                                value={settings.headingLevel}
                                onChange={(value) => handleChange('headingLevel', value)}
                                helpText="Choose the default heading level for table entries (e.g., h2, h3, h4)"
                                placeholder="h2"
                            />

                            <Select
                                label="Display position"
                                options={[
                                    { label: 'Before content', value: 'before' },
                                    { label: 'After content', value: 'after' },
                                ]}
                                value={settings.position}
                                onChange={(value) => handleChange('position', value)}
                                helpText="Where the table of contents appears relative to your content"
                            />

                            <Checkbox
                                label="Show on all pages automatically"
                                checked={settings.autoShow}
                                onChange={(value) => handleChange('autoShow', value)}
                                helpText="Automatically display table of contents on pages with headings"
                            />
                        </BlockStack>
                    </Card>

                    {/* Appearance Section */}
                    <Card>
                        <BlockStack gap="400">
                            <Text variant="headingMd" as="h2">Appearance</Text>

                            <Select
                                label="Theme"
                                options={[
                                    { label: 'Light', value: 'light' },
                                    { label: 'Dark', value: 'dark' },
                                    { label: 'Auto (match system)', value: 'auto' },
                                ]}
                                value={settings.theme}
                                onChange={(value) => handleChange('theme', value)}
                                helpText="Choose the color theme for your table of contents"
                            />

                            <Checkbox
                                label="Smooth scrolling"
                                checked={settings.smoothScroll}
                                onChange={(value) => handleChange('smoothScroll', value)}
                                helpText="Enable smooth scroll animation when clicking table entries"
                            />
                        </BlockStack>
                    </Card>
                </BlockStack>
            </Page>
        </>
    );
};

export default Settings;
