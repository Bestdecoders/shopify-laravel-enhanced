import React, { useState } from "react";
import { getHexColor, generateCSS, injectCSS } from "../hooks/helper";
import { log } from "../hooks/helper";
const SizeChartPreview = ({ chartData = {} }) => {
    const defaultPayload = {
        name: "Untitled Size Chart",
        tabs: {
            tab: false,
            firstTabName: "Size Chart",
            secondTabName: "Measurement Guide",
        },
        firstPage: "",
        secondPage: "",
        table: {
            mode: "cm",
            Converter: "yes",
            includeHeader: true,
            tableContent: [],
            isSticky: false
        },
        globalStyle: {}
    };

    // Merge with safe defaults and handle nested objects properly
    const payload = {
        ...defaultPayload,
        ...chartData,
        tabs: {
            ...defaultPayload.tabs,
            ...chartData.tabs
        },
        table: {
            ...defaultPayload.table,
            ...chartData.table
        },
        globalStyle: {
            ...defaultPayload.globalStyle,
            ...chartData.globalStyle
        }
    };
    // log(payload);

    const [activeTab, setActiveTab] = useState("first-tab");
    const [tableMode, setTableMode] = useState(payload.table.mode);

    const handleTabSwitch = (tab) => setActiveTab(tab);
    const handleModeChange = (mode) => setTableMode(mode);

    // Generate the CSS string
    const cssString = generateCSS(payload);
    injectCSS(cssString);
    // log(cssString);

    const convertData = (cell, index, mode, defaultMode, columnConditions) => {
        let { text, isBold, isUnconvertable } = cell;
        const regex = /^([0-9]*\.?[0-9]+-)*[0-9]*\.?[0-9]+$/;
        let output = text?.toString() || " ";

        const isConvertable =
            !isUnconvertable && columnConditions?.convertable !== false;
        const isBoldCell = isBold || columnConditions?.bold;

        if (
            !isConvertable ||
            !regex.test(output) ||
            mode.toLowerCase() === defaultMode.toLowerCase()
        ) {
            return isBoldCell ? <b>{output}</b> : output;
        }

        if (output.includes("-")) {
            const range = output
                .split("-")
                .map((value) =>
                    !isNaN(value)
                        ? mode === "in"
                            ? `${(value * 0.3937).toFixed(1)}″`
                            : `${(value * 2.54).toFixed(1)} cm`
                        : value
                );
            output = range.join("-");
        } else if (!isNaN(output)) {
            output =
                mode === "in"
                    ? `${(output * 0.3937).toFixed(1)}″`
                    : `${(output * 2.54).toFixed(1)} cm`;
        }

        return isBoldCell ? <b>{output}</b> : output;
    };

    const renderTable = () => {
        const { includeHeader, tableContent = [], mode, isSticky } = payload.table || {};

        if (!tableContent || tableContent.length === 0) {
            return (
                <div className="bds-table-wrapper">
                    <div className="bds-table-container">
                        <p style={{ textAlign: 'center', padding: '20px', color: '#666' }}>
                            No table data available. Add rows and columns in the table editor above.
                        </p>
                    </div>
                </div>
            );
        }

        const headers = includeHeader && tableContent.length > 0 
            ? (Array.isArray(tableContent[0]) ? tableContent[0] : (tableContent[0].cells || [])) 
            : [];
        const rows = includeHeader && tableContent.length > 1 ? tableContent.slice(1) : 
                     !includeHeader ? tableContent : [];

        return (
            <div className="bds-table-wrapper">
                {payload.table.Converter === "yes" && (
                    <div
                        className="bds-table-converter"
                        style={{ display: "flex" }}
                    >
                        <button
                            id="inch-tab"
                            data-mode="in"
                            className={tableMode === "in" ? "active" : ""}
                            onClick={() => handleModeChange("in")}
                        >
                            in
                        </button>
                        <div className="bds-vertical-hr"></div>
                        <button
                            id="cm-tab"
                            data-mode="cm"
                            className={tableMode === "cm" ? "active" : ""}
                            onClick={() => handleModeChange("cm")}
                        >
                            cm
                        </button>
                    </div>
                )}
                <div
                    className={`bds-table-container ${
                        isSticky ? "sticky-first-column" : ""
                    }`}
                >
                    <table>
                        {includeHeader && (
                            <thead>
                                <tr>
                                    {headers.map((header, index) => (
                                        <th key={index}>
                                            {header.text || " "}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                        )}
                        <tbody>
                            {rows.map((row, rowIndex) => {
                                // Handle both old format (array) and new format (object with cells)
                                const cells = Array.isArray(row) ? row : (row.cells || []);
                                if (!Array.isArray(cells)) {
                                    return null; // Skip invalid rows
                                }
                                return (
                                    <tr key={rowIndex}>
                                        {cells.map((cell, cellIndex) => {
                                            const columnConditions =
                                                headers[cellIndex] || {};
                                            return (
                                                <td key={cellIndex}>
                                                    {convertData(
                                                        cell || { text: '', isBold: false, isUnconvertable: false },
                                                        cellIndex,
                                                        tableMode,
                                                        mode,
                                                        columnConditions
                                                    )}
                                                </td>
                                            );
                                        })}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        );
    };

    return (
        <div className="bds-size-guide">
            <div className="bds-size-guide-header">
                <h2>SIZE GUIDE</h2>
                <div className="bds-title">{payload.name}</div>
            </div>
            {payload.tabs?.tab ? (
                <div className="bds-tabs">
                    <ul className="bds-tab-links">
                        <li
                            className={
                                activeTab === "first-tab" ? "active" : ""
                            }
                            onClick={() => handleTabSwitch("first-tab")}
                            data-tab="first-tab"
                        >
                            {payload.tabs.firstTabName || "Tab 1"}
                        </li>
                        <li
                            className={
                                activeTab === "second-tab" ? "active" : ""
                            }
                            onClick={() => handleTabSwitch("second-tab")}
                            data-tab="second-tab"
                        >
                            {payload.tabs.secondTabName || "Tab 2"}
                        </li>
                    </ul>
                    <div className="bds-tab-content">
                        <div
                            id="first-tab"
                            className={`bds-tab ${
                                activeTab === "first-tab" ? "active" : ""
                            }`}
                        >
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: payload.firstPage,
                                }}
                            />
                            {renderTable()}
                        </div>
                        <div
                            id="second-tab"
                            className={`bds-tab ${
                                activeTab === "second-tab" ? "active" : ""
                            }`}
                        >
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: payload.secondPage,
                                }}
                            />
                        </div>
                    </div>
                </div>
            ) : (
                <div className="bds-single-page">
                    <div
                        dangerouslySetInnerHTML={{
                            __html: payload.firstPage,
                        }}
                    />
                    {renderTable()}
                    <div
                        dangerouslySetInnerHTML={{
                            __html: payload.secondPage,
                        }}
                    />
                </div>
            )}

            <div className="Preview Button-block">
                <h2>Appearance Button Preview</h2>
                <button
                    className="bds-size-guide-icon-button bds-button"
                    data-micromodal-trigger="bds-popup-content"
                    style={{ 
                        display: 'inline-flex', 
                        alignItems: 'center', 
                        gap: '8px'
                    }}
                >
                    {payload?.globalStyle?.selectedIcon && 
                     payload?.globalStyle?.selectedIcon !== "none" && (
                        <img
                            src={`/images/svg-icons/${payload.globalStyle.selectedIcon}.svg`}
                            alt={payload.globalStyle.selectedIcon}
                            style={{
                                width: "20px",
                                height: "20px",
                            }}
                        />
                    )}

                    <span className="bds-btn-text">
                        {payload?.globalStyle?.buttonText || "Size Guide"}
                    </span>
                </button>
            </div>
        </div>
    );
};

export default SizeChartPreview;
