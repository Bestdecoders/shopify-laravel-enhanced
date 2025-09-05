import React, { useState, useEffect, useCallback, useRef } from "react";
import {
    Card,
    ResourceList,
    TextField,
    Page,
    Spinner,
    Button,
    Box,
    Text,
    InlineStack,
    Badge,
    Divider,
    BlockStack
} from "@shopify/polaris";
import axios from "axios";
import { sortItems } from "../hooks/helper"; // Ensure this is correctly imported from your helper file
import GeneralItem from "./CollectionItem";
import { useAxios } from "../hooks/useAxios";

export const Items = ({
    type,
    selectedItems,
    setSelectedItems,
    setVisible,
}) => {
    const [items, setItems] = useState({});
    const [query, setQuery] = useState("");
    const [cursor, setCursor] = useState(null);
    const [hasNextPage, setHasNextPage] = useState(true);
    const [loading, setLoading] = useState(false);
    const listRef = useRef(null);
    const axios = useAxios();

    const fetchItemsAxios = async (query, cursor, pageSize, includeSelectedItems = false) => {
        try {
            const typeString = Array.isArray(type) ? type[0] : type;
            const payload = {
                query,
                cursor,
                pageSize,
            };
            
            // Include selected items only on first load (no cursor) and when requested
            if (includeSelectedItems && !cursor && selectedItems && Object.keys(selectedItems).length > 0) {
                payload.selectedItems = Object.keys(selectedItems);
            }
            
            const response = await axios.post(`/get/list/${typeString}`, payload);
            console.log("Fetched data:", response.data); // Log the data to verify its structure
            return response.data; // Ensure this is the correct structure returned by your API
        } catch (error) {
            console.error("Error fetching items:", error);
            return { edges: [], pageInfo: { hasNextPage: false } };
        }
    };

  

    const handleSelectionChange = (item, select) => {
        const updatedSelectedItems = { ...selectedItems };

        // console.log("Updated selected items:", updatedSelectedItems);
        // console.log("Selected item:", item);
        // console.log("Select:", select);

        if (select) {
            updatedSelectedItems[item.id] = {};
        } else {
            delete updatedSelectedItems[item.id];
        }

        setSelectedItems(updatedSelectedItems);
    };

    const fetchItems = async (searchQuery, append = false) => {
        if (loading) return;
        setLoading(true);

        let itemData;
        try {
            // Include selected items only on the first load (not when appending)
            itemData = await fetchItemsAxios(
                searchQuery,
                append ? cursor : null,
                10,
                !append // includeSelectedItems = true only on first load
            );
            // console.log("Fetched items data:", itemData);
        } catch (error) {
            console.error("Error fetching items:", error);
            setLoading(false);
            return;
        }

        if (!itemData || !itemData.edges || !itemData.pageInfo) {
            console.error("Invalid item data format:", itemData);
            setLoading(false);
            return;
        }

        const { pageInfo, rows } = sortItems(itemData);
        // console.log("Sorted items data:", rows);

        if (append) {
            setItems((prevItems) => ({ ...prevItems, ...rows }));
        } else {
            setItems(rows);
        }

        setCursor(pageInfo.next.cursor);
        setHasNextPage(pageInfo.next.hasNext);
        setLoading(false);
    };

    const handleSearchChange = useCallback(
        (value) => {
            setQuery(value);
            fetchItems(value);
        },
        [fetchItems]
    );

    const handleScroll = () => {
        const { scrollTop, scrollHeight, clientHeight } = listRef.current;
        if (
            scrollHeight - scrollTop <= clientHeight * 1.5 &&
            hasNextPage &&
            !loading
        ) {
            fetchItems(query, true);
        }
    };

    useEffect(() => {
        fetchItems(query);
    }, []); // Ensure this is an empty dependency array to call on mount

    useEffect(() => {
        console.log("Items state:", items);
    }, [items]);

    useEffect(() => {
        console.log("selectedItems", selectedItems);
    }, [selectedItems]);

    const renderItem = (item) => {
        let renderedItem = null;

        renderedItem = (
            <GeneralItem
                key={item.id}
                item={item}
                selectedItems={selectedItems}
                handleSelection={handleSelectionChange}
            />
        );

        return renderedItem;
    };

    const selectedCount = Object.keys(selectedItems).length;
    
    // Handle type as array or string
    const typeString = Array.isArray(type) ? type[0] : type;
    const capitalizedType = typeString ? typeString.charAt(0).toUpperCase() + typeString.slice(1) : 'Item';
    
    return (
        <Box style={{ width: "500px", maxWidth: "90vw" }}>
            <Box padding="400">
                <BlockStack gap="400">
                    {/* Header */}
                    <Box>
                        <InlineStack align="space-between">
                            <Box>
                                <Text variant="headingMd" as="h2">
                                    Select {capitalizedType}s
                                </Text>
                                <Text variant="bodyMd" color="subdued">
                                    Choose items to associate with this size chart
                                </Text>
                            </Box>
                            {selectedCount > 0 && (
                                <Badge status="info">{selectedCount} selected</Badge>
                            )}
                        </InlineStack>
                    </Box>
                    <Divider />

                    {/* Search */}
                    <TextField
                        value={query}
                        onChange={handleSearchChange}
                        placeholder={`Search ${typeString}s...`}
                        autoComplete="off"
                        clearButton
                        onClearButtonClick={() => handleSearchChange("")}
                    />

                    {/* Items List */}
                    <Card>
                        <div
                            ref={listRef}
                            style={{ height: "400px", overflowY: "auto" }}
                            onScroll={handleScroll}
                        >
                            <ResourceList
                                resourceName={{ singular: typeString, plural: typeString + "s" }}
                                items={Object.values(items)}
                                renderItem={renderItem}
                            />
                            {loading && (
                                <Box padding="400" style={{ textAlign: "center" }}>
                                    <Spinner
                                        accessibilityLabel="Loading items"
                                        size="large"
                                    />
                                </Box>
                            )}
                            {Object.values(items).length === 0 && !loading && (
                                <Box padding="400" style={{ textAlign: "center" }}>
                                    <Text variant="bodyMd" color="subdued">
                                        {query ? `No ${typeString}s found matching "${query}"` : `No ${typeString}s available`}
                                    </Text>
                                </Box>
                            )}
                        </div>
                    </Card>

                    {/* Footer Actions */}
                    <Box 
                        style={{ 
                            position: 'sticky', 
                            bottom: 0, 
                            backgroundColor: 'white',
                            borderTop: '1px solid #e0e0e0',
                            marginTop: '16px'
                        }}
                        padding="400"
                    >
                        <InlineStack align="space-between">
                            <Text variant="bodyMd" color="subdued">
                                {selectedCount === 0 
                                    ? `No ${typeString}s selected` 
                                    : `${selectedCount} ${typeString}${selectedCount === 1 ? '' : 's'} selected`
                                }
                            </Text>
                            <Button 
                                variant="primary"
                                size="large"
                                onClick={() => setVisible && setVisible(false)}
                            >
                                Done
                            </Button>
                        </InlineStack>
                    </Box>
                </BlockStack>
            </Box>
        </Box>
    );
};
