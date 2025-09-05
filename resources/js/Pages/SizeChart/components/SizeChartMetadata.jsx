import { 
    Card, 
    TextField, 
    ChoiceList, 
    BlockStack, 
    Box, 
    Text,
    InlineGrid,
    Divider,
    Icon,
    InlineStack,
    Badge,
    Button,
    Tag,
    Modal,
    Scrollable
} from "@shopify/polaris";
import { InfoIcon, SearchIcon } from "@shopify/polaris-icons";
import { useCallback, useState, useMemo } from "react";
import { COUNTRIES, POPULAR_COUNTRIES } from "../../../constants/countries";

const SizeChartMetadata = ({ data, onChange, isPaidUser = true }) => {
    const [searchValue, setSearchValue] = useState('');
    const [isModalOpen, setIsModalOpen] = useState(false);
    
    const statusOptions = [
        { label: "Draft", value: "draft" },
        { label: "Active", value: "active" }
    ];

    const appearanceOptions = [
        { label: "Popup Modal", value: "popup" },
        { label: "Inline Display", value: "inline" }
    ];

    const handleNameChange = useCallback((value) => {
        onChange({ name: value });
    }, [onChange]);

    const handleStatusChange = useCallback((value) => {
        onChange({ status: value });
    }, [onChange]);

    const handleAppearanceChange = useCallback((value) => {
        onChange({ appearance: value });
    }, [onChange]);

    // Handle selected countries - ensure it's always an array
    const selectedCountries = useMemo(() => {
        if (!data.country) return [];
        if (Array.isArray(data.country)) return data.country;
        // Handle legacy string format
        if (typeof data.country === 'string') {
            try {
                const parsed = JSON.parse(data.country);
                return Array.isArray(parsed) ? parsed : [data.country];
            } catch {
                return [data.country];
            }
        }
        return [];
    }, [data.country]);

    // Filter countries based on search input
    const filteredCountries = useMemo(() => {
        if (!searchValue) return POPULAR_COUNTRIES;
        return COUNTRIES.filter(country =>
            country.label.toLowerCase().includes(searchValue.toLowerCase())
        );
    }, [searchValue]);

    const handleCountrySelect = useCallback((countryValue) => {
        const isSelected = selectedCountries.some(c => c === countryValue);
        let newSelection;
        
        if (isSelected) {
            // Remove country
            newSelection = selectedCountries.filter(c => c !== countryValue);
        } else {
            // Add country
            newSelection = [...selectedCountries, countryValue];
        }
        
        onChange({ country: newSelection });
        // Keep modal open for multiple selections
    }, [selectedCountries, onChange]);

    const handleRemoveCountry = useCallback((countryValue) => {
        const newSelection = selectedCountries.filter(c => c !== countryValue);
        onChange({ country: newSelection });
    }, [selectedCountries, onChange]);

    // Get country label by value
    const getCountryLabel = useCallback((value) => {
        const country = COUNTRIES.find(c => c.value === value);
        return country ? country.label : value;
    }, []);

    // Handle modal actions
    const handleOpenModal = useCallback(() => {
        setIsModalOpen(true);
        setSearchValue(''); // Clear search when opening
    }, []);

    const handleCloseModal = useCallback(() => {
        setIsModalOpen(false);
        setSearchValue('');
    }, []);

    const handleClearAll = useCallback(() => {
        onChange({ country: [] });
    }, [onChange]);

    return (
        <Card>
            <Box padding="500">
                <Box paddingBlockEnd="400">
                    <InlineStack align="space-between">
                        <Box>
                            <Text variant="headingMd" as="h2">
                                Basic Information
                            </Text>
                            <Text variant="bodyMd" color="subdued">
                                Configure the fundamental settings for your size chart
                            </Text>
                        </Box>
                        <Icon source={InfoIcon} color="subdued" />
                    </InlineStack>
                </Box>
                <Divider />
                <Box paddingBlockStart="500">
                    <BlockStack gap="500">
                        <TextField
                            label="Size Chart Name"
                            value={data.name || ""}
                            onChange={handleNameChange}
                            placeholder="e.g., Men's T-Shirt Sizing Guide"
                            autoComplete="off"
                            helpText="Choose a clear, descriptive name that helps identify this size chart"
                        />

                        <InlineGrid columns={2} gap="400">
                            <Box>
                                <ChoiceList
                                    title="Publishing Status"
                                    choices={statusOptions}
                                    selected={data.status || ["draft"]}
                                    onChange={handleStatusChange}
                                />
                                {data.status?.[0] === "active" && (
                                    <Box paddingBlockStart="200">
                                        <Badge status="success">Live on store</Badge>
                                    </Box>
                                )}
                                {data.status?.[0] === "draft" && (
                                    <Box paddingBlockStart="200">
                                        <Badge>Draft mode</Badge>
                                    </Box>
                                )}
                            </Box>

                            <Box>
                                <ChoiceList
                                    title="Display Style"
                                    choices={appearanceOptions}
                                    selected={data.appearance || ["popup"]}
                                    onChange={handleAppearanceChange}
                                />
                            </Box>
                        </InlineGrid>

                        {isPaidUser && (
                            <Box>
                                <Text variant="bodyMd" fontWeight="medium">
                                    Target Countries/Regions
                                </Text>
                                <Box paddingBlockStart="200">
                                    <Text variant="bodyMd" color="subdued">
                                        Select countries where this size chart will be used for localized sizing standards
                                    </Text>
                                </Box>
                                
                                {/* Selected Countries Display */}
                                {selectedCountries.length > 0 && (
                                    <Box paddingBlockStart="300">
                                        <InlineStack gap="200" wrap>
                                            {selectedCountries.map((countryValue) => (
                                                <Tag
                                                    key={countryValue}
                                                    onRemove={() => handleRemoveCountry(countryValue)}
                                                >
                                                    {getCountryLabel(countryValue)}
                                                </Tag>
                                            ))}
                                        </InlineStack>
                                    </Box>
                                )}

                                {/* Country Selection Trigger */}
                                <Box paddingBlockStart="300">
                                    <Button
                                        fullWidth
                                        textAlign="left"
                                        onClick={handleOpenModal}
                                        icon={SearchIcon}
                                        disclosure="down"
                                    >
                                        <InlineStack align="space-between">
                                            <Text variant="bodyMd">
                                                {selectedCountries.length > 0 
                                                    ? `${selectedCountries.length} countries selected` 
                                                    : 'Select target countries'
                                                }
                                            </Text>
                                        </InlineStack>
                                    </Button>
                                </Box>

                                {/* Country Selection Modal */}
                                <Modal
                                    open={isModalOpen}
                                    onClose={handleCloseModal}
                                    title="Select Target Countries"
                                    primaryAction={{
                                        content: `Done (${selectedCountries.length} selected)`,
                                        onAction: handleCloseModal,
                                    }}
                                    secondaryActions={[
                                        {
                                            content: 'Clear All',
                                            onAction: handleClearAll,
                                            disabled: selectedCountries.length === 0,
                                            destructive: true
                                        }
                                    ]}
                                    size="large"
                                >
                                    <Modal.Section>
                                        <BlockStack gap="400">
                                            {/* Search Field */}
                                            <TextField
                                                prefix={<Icon source={SearchIcon} />}
                                                value={searchValue}
                                                onChange={setSearchValue}
                                                placeholder="Search countries..."
                                                clearButton
                                                onClearButtonClick={() => setSearchValue('')}
                                                autoComplete="off"
                                            />

                                            {/* Selected Countries Section */}
                                            {selectedCountries.length > 0 && (
                                                <Box>
                                                    <Box paddingBlockEnd="300">
                                                        <Text variant="headingMd" as="h3">
                                                            Selected Countries ({selectedCountries.length})
                                                        </Text>
                                                    </Box>
                                                    <InlineStack gap="200" wrap>
                                                        {selectedCountries.map((countryValue) => (
                                                            <Tag
                                                                key={countryValue}
                                                                onRemove={() => handleRemoveCountry(countryValue)}
                                                            >
                                                                {getCountryLabel(countryValue)}
                                                            </Tag>
                                                        ))}
                                                    </InlineStack>
                                                    <Box paddingBlockStart="400">
                                                        <Divider />
                                                    </Box>
                                                </Box>
                                            )}

                                            {/* Available Countries Section */}
                                            <Box>
                                                <Box paddingBlockEnd="300">
                                                    <Text variant="headingMd" as="h3">
                                                        {!searchValue ? 'Popular Countries' : `Search Results (${filteredCountries.length})`}
                                                    </Text>
                                                </Box>
                                                
                                                <Scrollable style={{ height: '400px' }}>
                                                    <BlockStack gap="100">
                                                        {filteredCountries.length > 0 ? (
                                                            filteredCountries.map((country) => {
                                                                const isSelected = selectedCountries.includes(country.value);
                                                                return (
                                                                    <Box
                                                                        key={country.value}
                                                                        padding="300"
                                                                        style={{
                                                                            cursor: 'pointer',
                                                                            backgroundColor: isSelected ? '#e7f3ff' : 'transparent',
                                                                            borderRadius: '8px',
                                                                            border: isSelected ? '2px solid #0066cc' : '2px solid transparent',
                                                                            transition: 'all 0.2s ease'
                                                                        }}
                                                                        onClick={() => handleCountrySelect(country.value)}
                                                                        onMouseEnter={(e) => {
                                                                            if (!isSelected) {
                                                                                e.currentTarget.style.backgroundColor = '#f8f9fa';
                                                                                e.currentTarget.style.border = '2px solid #e8e8e8';
                                                                            }
                                                                        }}
                                                                        onMouseLeave={(e) => {
                                                                            if (!isSelected) {
                                                                                e.currentTarget.style.backgroundColor = 'transparent';
                                                                                e.currentTarget.style.border = '2px solid transparent';
                                                                            }
                                                                        }}
                                                                    >
                                                                        <InlineStack align="space-between">
                                                                            <Text variant="bodyMd" fontWeight={isSelected ? "semibold" : "regular"}>
                                                                                {country.label}
                                                                            </Text>
                                                                            {isSelected && (
                                                                                <Badge status="success" size="small">
                                                                                    ✓ Selected
                                                                                </Badge>
                                                                            )}
                                                                        </InlineStack>
                                                                    </Box>
                                                                );
                                                            })
                                                        ) : (
                                                            <Box padding="600" textAlign="center">
                                                                <BlockStack gap="200">
                                                                    <Text variant="bodyLg" color="subdued">
                                                                        No countries found
                                                                    </Text>
                                                                    <Text variant="bodyMd" color="subdued">
                                                                        Try adjusting your search term
                                                                    </Text>
                                                                </BlockStack>
                                                            </Box>
                                                        )}
                                                    </BlockStack>
                                                </Scrollable>
                                            </Box>
                                        </BlockStack>
                                    </Modal.Section>
                                </Modal>
                            </Box>
                        )}
                    </BlockStack>
                </Box>
            </Box>
        </Card>
    );
};

export default SizeChartMetadata;