import { hsbToHex } from "@shopify/polaris";
import { is } from "date-fns/locale";

export const getShopNameFromSession = () => {
    const storedData = sessionStorage.getItem("shopifyConfig");
    let shopName = null;
    if (!storedData) {
        console.log("No Shopify config found in sessionStorage.");
        return null; // or return a default value or handle as needed
    }

    // Parse the stored JSON string back into an object
    const config = JSON.parse(storedData);
    const shopUrl = config?.host; // Assuming 'host' contains the full shop URL

    if (!shopUrl) {
        console.log("Shop URL not found in Shopify config.");
        return null; // or return a default value or handle as needed
    }

    try {
        // If the URL is base64 encoded in 'host', decode it
        const decodedUrl = atob(shopUrl);
        // Extract the shop name from the decoded URL
        const urlParts = decodedUrl.split("/");
        shopName = urlParts[urlParts.length - 1]; // Assuming the shop name is the last part of the URL
    } catch (e) {
        // If there's an error decoding, the URL might not be encoded, try to extract shop name directly
        const urlParts = shopUrl.split("/");
        shopName = urlParts[urlParts.length - 1];
    }
    if (shopName) {
        return shopName + ".myshopify.com";
    }
};

export const sortItems = (items) => {
    let pageInfo = {
        next: { hasNext: items?.pageInfo.hasNextPage },
        prev: { hasPrev: items?.pageInfo.hasPreviousPage },
    };
    let rows = {}; // Initialize rows as an object
    let productCursor = items?.productCursor;

    items.edges.forEach((value, key, array) => {
        const uniqueKey = extractShopifyId(value?.node?.id); // Assuming this function returns a unique ID

        if (key === 0) {
            pageInfo.prev["cursor"] = value.cursor;
        }

        if (key + 1 === array.length) {
            pageInfo.next["cursor"] = value.cursor;
        }

        // Add the item under the uniqueKey
        rows[uniqueKey] = {
            id: uniqueKey,
            title: value?.node?.title,
            vendor: value?.node?.vendor,
            type: value?.node?.productType,
            status: value?.node?.status,
            image:
                value?.node?.featuredImage?.url ||
                value?.node?.image?.src ||
                value?.node?.avatar?.src,
            firstName: value?.node?.firstName,
            lastName: value?.node?.lastName,
            email: value?.node?.email,
            productsCount: value?.node?.productsCount,
            totalInventory: value?.node?.totalInventory,
            tracksInventory: value?.node?.tracksInventory,
            price: value?.node?.variants?.edges[0]?.node?.price,
            variants: value?.node?.variants?.edges.map((variantEdge) => ({
                id: extractShopifyId(variantEdge.node.id),
                title: variantEdge.node.title,
                price: variantEdge.node.price,
                availableForSale: variantEdge.node.availableForSale,
                available: variantEdge.node.inventoryQuantity,
            })),
        };
    });

    // If product cursor, add it into pageInfo
    if (productCursor) {
        pageInfo["productCursor"] = productCursor;
    }

    return { pageInfo, rows };
};

export const extractShopifyId = (gid) => {
    if (!gid) return null;
    // Split the GID by '/' and take the last part, which is the numerical ID
    const parts = gid.split("/");
    const numericalId = parts.pop(); // Get the last element, which should be the ID
    return numericalId;
};
export const sortCollections = (collection) => {
    let pageInfo = {
        next: { hasNext: collection.pageInfo.hasNextPage },
        prev: { hasPrev: collection.pageInfo.hasPreviousPage },
    };
    let rows = [];
    collection.edges.map((value, key, { length }) => {
        if (key == 0) {
            pageInfo.prev["cursor"] = value.cursor;
        }

        if (key + 1 == length) {
            pageInfo.next["cursor"] = value.cursor;
        }

        rows.push({
            id: value?.node?.id,
            title: value?.node?.title,
            image: value?.node?.image?.url,
        });
    });
    return { pageInfo, rows };
};

export const formatTableData = (tableString) => {
    // Parse the JSON string into an object
    const tableObject = tableString;

    if (tableObject == null || tableObject.tableContent?.length == 0) {
        return {
            showCreateForm: true,
            mode: "cm",
            includeHeader: true,
            Converter: "yes",
            isSticky: false,
        };
    }

    // Transform data rows into an array of objects with cell details
    const formattedData = tableObject.tableContent.map((row) => ({
        cells: row.map((cell, index) => ({
            text: cell.text || "",
            isBold: cell.isBold || false,
            isHeader: cell.isHeader || false,
            isUnconvertable: cell.isUnconvertable || false,
            ...cell,
        })),
    }));
    // if not isset tableObject.includeHeader
    const tableContent = formattedData;

    // Create the final object structure with other info as separate keys
    const formattedTable = {
        mode: tableObject.mode,
        Converter: tableObject.Converter,
        includeHeader: tableObject.includeHeader,
        showCreateForm: false,
        isSticky: tableObject.isSticky,
        tableContent: tableContent,
    };
    // console.log({
    //     formattedTable});

    // Return the formatted table object with other information
    return formattedTable;
};

export const convertTableContent = (tableData) => {
    const tableContent = tableData?.tableContent;

    // If tableContent is not an array, return the original tableData without alteration
    if (!Array.isArray(tableContent)) {
        return {
            ...tableData,
            tableContent: [],
        };
    }

    // Create a new tableData object with the modified tableContent
    return {
        ...tableData,
        tableContent: tableContent.map((row) => row.cells),
    };
};

/**
 * Helper function to extract a global color from payload and convert it to hex.
 */
export const getHexColor = (payload, pathKey) => {
    const color = pathKey.split(".").reduce((acc, key) => acc?.[key], payload);
    if (color) {
        return hsbToHex({
            hue: color.hue,
            saturation: color.saturation,
            brightness: color.brightness,
            alpha: color.alpha,
        });
    }
    return undefined;
};

/**
 * Generate a CSS string based on global styles and cell-specific styles.
 * @param {Object} payload - The global styles payload.
 * @param {Array} tableContent - Array of table rows with cell data.
 * @returns {string} - The generated CSS string.
 */
export const generateCSS = (payload) => {
    const tableContent = payload?.table?.tableContent || [];
    const includeHeader = payload?.table?.includeHeader || false;

    // Start with global styles
    let css = `
        .bds-size-guide .bds-table-container tr td {
            background-color: ${
                getHexColor(payload, "globalStyle.globalTableBGColor") ||
                "#ffffff"
            };
            color: ${
                getHexColor(payload, "globalStyle.globalTableCellColor") ||
                "#000000"
            };
        }
        .bds-size-guide .bds-table-container thead th {
            background-color: ${
                getHexColor(payload, "globalStyle.tableHeaderBGColor") ||
                "#dddddd"
            };
            color: ${
                getHexColor(payload, "globalStyle.tableHeaderColor") ||
                "#000000"
            };
        }
    
     /* Convert Button Default Styles */
     .bds-table-converter button {
        background-color: ${
            getHexColor(payload, "globalStyle.converterButtonBGColor") ||
            "#f0f0f0"
        };
        color: ${
            getHexColor(payload, "globalStyle.converterButtonTextColor") ||
            "#000000"
        };
        border: 1px solid ${
            getHexColor(payload, "globalStyle.converterButtonBorderColor") ||
            "#cccccc"
        };
        padding: 10px 20px;
        border-radius: 5px;
        margin: 0 5px;
        cursor: pointer;
    }

    /* Convert Button Active Styles */
    .bds-table-converter button.active {
        background-color: ${
            getHexColor(payload, "globalStyle.converterButtonActiveBGColor") ||
            "#007bff"
        };
        color: ${
            getHexColor(
                payload,
                "globalStyle.converterButtonActiveTextColor"
            ) || "#ffffff"
        };
        border: 1px solid ${
            getHexColor(
                payload,
                "globalStyle.converterButtonActiveBorderColor"
            ) || "#0056b3"
        };
    }

    /* Tab Default Styles */
    .bds-tab-links li {
        background-color: ${
            getHexColor(payload, "globalStyle.tabButtonBGColor") || "#f8f8f8"
        };
        color: ${
            getHexColor(payload, "globalStyle.tabButtonTextColor") || "#000000"
        };
        border: 1px solid ${
            getHexColor(payload, "globalStyle.tabButtonBorderColor") ||
            "#dddddd"
        };
        padding: 10px 15px;
        border-radius: 5px;
        display: inline-block;
        margin-right: 5px;
        cursor: pointer;
    }

    /* Tab Active Styles */
    .bds-tab-links li.active {
        background-color: ${
            getHexColor(payload, "globalStyle.tabButtonActiveBGColor") ||
            "#28a745"
        };
        color: ${
            getHexColor(payload, "globalStyle.tabButtonActiveTextColor") ||
            "#ffffff"
        };
        border: 1px solid ${
            getHexColor(payload, "globalStyle.tabButtonActiveBorderColor") ||
            "#1e7e34"
        };
    }
           /* Trigger Button Styles */
        .bds-button {
            background-color: ${
                getHexColor(payload, "globalStyle.buttonBGColor") || "#007bff"
            };
            color: ${
                getHexColor(payload, "globalStyle.buttonTextColor") || "#ffffff"
            };
            border: 1px solid ${
                getHexColor(payload, "globalStyle.buttonBorderColor") ||
                "#0056b3"
            };
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        /* Trigger Button Hover Styles */
        .bds-button:hover {
            background-color: ${
                getHexColor(payload, "globalStyle.buttonHoverBGColor") ||
                "#0056b3"
            };
            color: ${
                getHexColor(payload, "globalStyle.buttonHoverTextColor") ||
                "#ffffff"
            };
            border: 1px solid ${
                getHexColor(payload, "globalStyle.buttonHoverBorderColor") ||
                "#004085"
            };
        }
        
`;

    // Iterate through table content
    tableContent.forEach((row, rowIndex) => {
        row.forEach((cell, cellIndex) => {
            // Header row styles
            if (includeHeader && rowIndex === 0) {
                if (cell.backgroundColor || cell.textColor) {
                    css += `
                        .bds-size-guide .bds-table-container thead th:nth-child(${
                            cellIndex + 1
                        }) {
                            background-color: ${
                                cell.backgroundColor
                                    ? hsbToHex(cell.backgroundColor)
                                    : "inherit"
                            };
                            color: ${
                                cell.textColor
                                    ? hsbToHex(cell.textColor)
                                    : "inherit"
                            };
                        }
                    `;
                }
            } else {
                // Body row styles
                const adjustedRowIndex = includeHeader
                    ? rowIndex
                    : rowIndex + 1;
                if (cell.backgroundColor || cell.textColor) {
                    css += `
                        .bds-size-guide .bds-table-container tr:nth-child(${adjustedRowIndex}) td:nth-child(${
                        cellIndex + 1
                    }) {
                            background-color: ${
                                cell.backgroundColor
                                    ? hsbToHex(cell.backgroundColor)
                                    : "inherit"
                            };
                            color: ${
                                cell.textColor
                                    ? hsbToHex(cell.textColor)
                                    : "inherit"
                            };
                        }
                    `;
                }
            }
        });
    });

    /* Custom Styles from globalStyle.globalStyle */
    css += `${payload?.globalStyle?.customStyle || ""}`;

    return css;
};

export const injectCSS = (cssString) => {
    let styleTag = document.getElementById("dynamic-styles");

    if (!styleTag) {
        styleTag = document.createElement("style");
        styleTag.id = "dynamic-styles";
        document.head.appendChild(styleTag);
    }

    styleTag.innerHTML = cssString;
};

export const log = (...args) => {
    if (import.meta.env.VITE_APP_DEBUG === "true") {
        console.log(...args);
    }
};
