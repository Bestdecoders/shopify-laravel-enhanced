import React, { useState, useCallback, useEffect } from "react";
import {
    Popover,
    TextField,
    Icon,
    InlineStack,
    Button,
    Box,
    BlockStack,
    Text,
    Thumbnail,
    Card,
    Badge,
    Divider,
} from "@shopify/polaris";

import { ContentFilledIcon } from "@shopify/polaris-icons";
import { SearchIcon } from "@shopify/polaris-icons";
import { Items } from "./Items";
import { getShopNameFromSession } from "../hooks/helper";
import { useAxios } from "../hooks/useAxios";
import { extractShopifyId } from "../hooks/helper";
const IterateItems = ({
    type = ["product"],
    selectedItems = {},
    setSelectedItems,
}) => {
    const [visible, setVisible] = useState(false);
    const [items, setItems] = useState({});
    const [isLoading, setIsLoading] = useState(false);
    const axios = useAxios();

    // Ensure we always have a valid type array
    const safeType = Array.isArray(type) ? type : [type || "product"];
    const safeSelectedItems = selectedItems || {};
    const capitalizeFirstLetterWithS = (string) => {
        if (!string || typeof string !== "string") {
            return "Products"; // Safe fallback
        }
        return string.charAt(0).toUpperCase() + string.slice(1) + "s";
    };

    const deleteItem = (item) => {
        let updatedSelectedItems = {};

        if (safeType[0] === "product") {
            // console.log("item", item);

            updatedSelectedItems = { ...safeSelectedItems };
            // console.log("updatedSelectedItems", updatedSelectedItems);

            delete updatedSelectedItems[item.legacyResourceId];
        } else {
            item = extractShopifyId(item.id);
            updatedSelectedItems = Object.keys(safeSelectedItems).reduce(
                (acc, key) => {
                    if (key !== item) {
                        acc[key] = safeSelectedItems[key];
                    }
                    return acc;
                },
                {}
            );
        }

        // If the updatedSelectedItems is now empty, explicitly set it to an empty object
        if (typeof setSelectedItems === "function") {
            if (Object.keys(updatedSelectedItems).length === 0) {
                setSelectedItems({});
            } else {
                setSelectedItems(updatedSelectedItems);
            }
        }
    };

    useEffect(() => {
        // console.log("Fetch Items is commented ....");
        // if (safeType[0]) {
        //     fetchItems(safeType[0], safeSelectedItems);
        // }
    }, [selectedItems]);

    const fetchItems = (type, items) => {
        const itemIds = Object.keys(items || {});

        if (isLoading || itemIds.length === 0) return;

        const formattedItems = Object.keys(items || {});
        const capitalizedType = capitalizeFirstLetterWithS(type || "product");
        setIsLoading(true);

        axios({
            method: "post",
            url: `/get/assets`,
            data: {
                itemType: capitalizedType,
                items: formattedItems,
            },
        })
            .then((response) => {
                response = response.data;
                setItems(response);
                setIsLoading(false);
            })
            .catch((error) => {
                console.log(error);
                setIsLoading(false);
            });
    };

    const handleOnClose = () => {
        setVisible(false);
    };

    const label = (typeArray) => {
        if (!typeArray || !Array.isArray(typeArray) || typeArray.length === 0) {
            return "Products"; // Default fallback
        }
        return capitalizeFirstLetterWithS(typeArray[0]);
    };

    const selectedCount = Object.keys(safeSelectedItems).length;

    const activator = (
        <BlockStack gap="400">
            <InlineStack gap="400" align="end">
                <Box style={{ flex: 1 }}>
                    <TextField
                        label={label(safeType)}
                        onChange={() => {}}
                        readOnly
                        placeholder={`Click Browse to select ${
                            safeType[0] || "product"
                        }s`}
                        prefix={<Icon source={SearchIcon} />}
                        autoComplete="off"
                        helpText={
                            selectedCount > 0
                                ? `${selectedCount} ${safeType[0]}${
                                      selectedCount === 1 ? "" : "s"
                                  } selected`
                                : undefined
                        }
                    />
                </Box>
                <Button onClick={() => setVisible(true)} variant="primary">
                    Browse {label(safeType)}
                </Button>
            </InlineStack>

            {selectedCount > 0 && (
                <Card>
                    <Box paddingBlockEnd="200">
                        <InlineStack align="space-between">
                            <Text variant="headingXs" as="h4">
                                Selected {label(safeType)}
                            </Text>
                            <Badge status="info">{selectedCount}</Badge>
                        </InlineStack>
                    </Box>
                    <Divider />
                    <Box paddingBlockStart="300">
                        <BlockStack gap="300">
                            {items &&
                                Object.entries(items).map(([id, item]) => {
                                    let image =
                                        item?.featuredImage?.originalSrc ||
                                        item?.image?.src ||
                                        item?.avatar?.src;

                                    let media = image ? (
                                        <Thumbnail
                                            source={image}
                                            alt={item?.title || item?.firstName}
                                            size="small"
                                        />
                                    ) : (
                                        <Thumbnail
                                            source={ContentFilledIcon}
                                            alt={item?.title || item?.firstName}
                                            size="small"
                                        />
                                    );

                                    return (
                                        <InlineStack
                                            gap="300"
                                            align="center"
                                            key={id}
                                        >
                                            {media}
                                            <Box style={{ flex: 1 }}>
                                                <Text
                                                    variant="bodyMd"
                                                    as="p"
                                                    truncate
                                                >
                                                    {item?.title ||
                                                        item?.firstName +
                                                            " " +
                                                            item?.lastName}
                                                </Text>
                                            </Box>
                                            <Button
                                                onClick={() => deleteItem(item)}
                                                variant="plain"
                                                tone="critical"
                                                size="micro"
                                            >
                                                Remove
                                            </Button>
                                        </InlineStack>
                                    );
                                })}
                        </BlockStack>
                    </Box>
                </Card>
            )}
        </BlockStack>
    );

    return (
        <div className="custom-popover">
            <Popover
                active={visible}
                activator={activator}
                onClose={handleOnClose}
                autofocusTarget="first-node"
                fluidContent
                preferredPosition="above"
                preferredAlignment="center"
            >
                <Popover.Section>
                    <Items
                        type={safeType}
                        selectedItems={safeSelectedItems}
                        setSelectedItems={setSelectedItems}
                        setVisible={setVisible}
                    />
                </Popover.Section>
            </Popover>
        </div>
    );
};

export default IterateItems;
