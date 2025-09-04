import React, { useState, useCallback } from "react";
import {
    Page,
    Layout,
    Card,
    Button,
    TextField,
    DataTable,
    Popover,
    ActionList,
    Box,
    Text,
    BlockStack,
    InlineStack,
    Checkbox,
} from "@shopify/polaris";
import {
    PlusCircleIcon,
    DeleteIcon,
    EditIcon,
    WrenchIcon,
    StarIcon,
} from "@shopify/polaris-icons";
import "../../css/table-editor.css";

export default function TableEditor() {
    const [tableData, setTableData] = useState([]);
    const [showCreateForm, setShowCreateForm] = useState(true);
    const [columns, setColumns] = useState(3);
    const [rows, setRows] = useState(3);
    const [includeHeader, setIncludeHeader] = useState(false);
    const [activePopover, setActivePopover] = useState(null);

    const createTable = () => {
        const headerRow = includeHeader
            ? {
                  cells: Array(columns)
                      .fill(null)
                      .map((_, index) => ({
                          text: `Column ${index + 1}`,
                          isBold: false,
                          isHeader: true,
                          isUnconvertable: false,
                      })),
              }
            : null;

        const dataRows = Array(rows)
            .fill(null)
            .map(() => ({
                cells: Array(columns)
                    .fill(null)
                    .map(() => ({
                        text: "",
                        isBold: false,
                        isHeader: false,
                        isUnconvertable: false,
                    })),
            }));

        setTableData(headerRow ? [headerRow, ...dataRows] : dataRows);
        setShowCreateForm(false);
    };

    const deleteTable = () => {
        setTableData([]);
        setShowCreateForm(true);
    };

    const addColumn = (index, position) => {
        setTableData((prevData) =>
            prevData.map((row) => ({
                cells: [
                    ...row.cells.slice(
                        0,
                        position === "after" ? index + 1 : index
                    ),
                    {
                        text: row.cells[0].isHeader
                            ? `Column ${prevData[0].cells.length + 1}`
                            : "",
                        isBold: false,
                        isHeader: row.cells[0].isHeader,
                        isUnconvertable: false,
                    },
                    ...row.cells.slice(
                        position === "after" ? index + 1 : index
                    ),
                ],
            }))
        );
        setActivePopover(null);
    };

    const deleteColumn = (index) => {
        setTableData((prevData) =>
            prevData.map((row) => ({
                cells: row.cells.filter((_, colIndex) => colIndex !== index),
            }))
        );
        setActivePopover(null);
    };

    const addRow = (index, position) => {
        const newRow = {
            cells: Array(tableData[0]?.cells.length || 0)
                .fill(null)
                .map(() => ({
                    text: "",
                    isBold: false,
                    isHeader: false,
                    isUnconvertable: false,
                })),
        };
        setTableData((prevData) => [
            ...prevData.slice(0, position === "after" ? index + 1 : index),
            newRow,
            ...prevData.slice(position === "after" ? index + 1 : index),
        ]);
        setActivePopover(null);
    };

    const deleteRow = (index) => {
        setTableData((prevData) =>
            prevData.filter((_, rowIndex) => rowIndex !== index)
        );
        setActivePopover(null);
    };

    const toggleBold = (rowIndex, colIndex) => {
        setTableData((prevData) =>
            prevData.map((row, rIndex) =>
                rIndex === rowIndex
                    ? {
                          cells: row.cells.map((cell, cIndex) =>
                              cIndex === colIndex
                                  ? { ...cell, isBold: !cell.isBold }
                                  : cell
                          ),
                      }
                    : row
            )
        );
    };

    const toggleUnconvertable = (colIndex) => {
        setTableData((prevData) =>
            prevData.map((row, rowIndex) => ({
                cells: row.cells.map((cell, cIndex) =>
                    cIndex === colIndex
                        ? { ...cell, isUnconvertable: !cell.isUnconvertable }
                        : cell
                ),
            }))
        );
    };

    const handleCellChange = (rowIndex, colIndex, value) => {
        setTableData((prevData) =>
            prevData.map((row, rIndex) =>
                rIndex === rowIndex
                    ? {
                          cells: row.cells.map((cell, cIndex) =>
                              cIndex === colIndex
                                  ? { ...cell, text: value }
                                  : cell
                          ),
                      }
                    : row
            )
        );
    };

    const generateJSON = () => {
        const headers = {};
        const data = {};

        tableData.forEach((row, rowIndex) => {
            if (row.cells[0].isHeader) {
                row.cells.forEach((cell, colIndex) => {
                    headers[colIndex] = `${
                        cell.isBold ? `*${cell.text}*` : cell.text
                    }${cell.isUnconvertable ? "**" : ""}`;
                });
            } else {
                data[rowIndex - (includeHeader ? 1 : 0)] = {};
                row.cells.forEach((cell, colIndex) => {
                    data[rowIndex - (includeHeader ? 1 : 0)][colIndex] = `${
                        cell.isBold ? `*${cell.text}*` : cell.text
                    }${cell.isUnconvertable ? "**" : ""}`;
                });
            }
        });

        const result = {
            mode: "cm",
            Converter: "yes",
            ...(includeHeader && { headers }),
            data,
        };

        return JSON.stringify(result, null, 2);
    };

    const togglePopover = useCallback(
        (rowIndex, colIndex) => () => {
            setActivePopover(
                activePopover?.row === rowIndex &&
                    activePopover?.col === colIndex
                    ? null
                    : { row: rowIndex, col: colIndex }
            );
        },
        [activePopover]
    );

    const closePopover = useCallback(() => setActivePopover(null), []);

    const renderCell = (rowIndex, colIndex, cell) => {
        const isHeader = cell.isHeader;
        const popoverItems = [
            ...(isHeader
                ? [
                      {
                          content: cell.isUnconvertable
                              ? "Make Column Convertable"
                              : "Make Column Unconvertable",
                          onAction: () => toggleUnconvertable(colIndex),
                      },
                  ]
                : [
                      {
                          content: "Toggle Bold",
                          onAction: () => toggleBold(rowIndex, colIndex),
                      },
                  ]),
            {
                content: "Add Column Before",
                onAction: () => addColumn(colIndex, "before"),
            },
            {
                content: "Add Column After",
                onAction: () => addColumn(colIndex, "after"),
            },
            {
                content: "Delete Column",
                onAction: () => deleteColumn(colIndex),
            },
            {
                content: "Add Row Before",
                onAction: () => addRow(rowIndex, "before"),
            },
            {
                content: "Add Row After",
                onAction: () => addRow(rowIndex, "after"),
            },
            {
                content: "Delete Row",
                onAction: () => deleteRow(rowIndex),
            },
        ];

        return (
            <Box position="relative">
                <TextField
                    value={cell.text}
                    onChange={(value) =>
                        handleCellChange(rowIndex, colIndex, value)
                    }
                    autoComplete="off"
                />
                <Popover
                    active={
                        activePopover?.row === rowIndex &&
                        activePopover?.col === colIndex
                    }
                    activator={
                        <div className="popup-btn">
                            <Button
                                onClick={togglePopover(rowIndex, colIndex)}
                                icon={WrenchIcon}
                                size="slim"
                            />
                        </div>
                    }
                    onClose={closePopover}
                >
                    <ActionList actionRole="menuitem" items={popoverItems} />
                </Popover>
            </Box>
        );
    };

    return (
        <Page fullWidth>
            <Layout>
                <Layout.Section>
                    {showCreateForm ? (
                        <Card>
                            <BlockStack gap="400">
                                <Text as="h2" variant="headingMd">
                                    Create Table
                                </Text>
                                <TextField
                                    label="Number of Columns"
                                    type="number"
                                    value={columns.toString()}
                                    onChange={(value) =>
                                        setColumns(parseInt(value))
                                    }
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Number of Rows"
                                    type="number"
                                    value={rows.toString()}
                                    onChange={(value) =>
                                        setRows(parseInt(value))
                                    }
                                    autoComplete="off"
                                />
                                <Checkbox
                                    label="Include Header"
                                    checked={includeHeader}
                                    onChange={() =>
                                        setIncludeHeader(!includeHeader)
                                    }
                                />
                                <Button onClick={createTable}>
                                    Create Table
                                </Button>
                            </BlockStack>
                        </Card>
                    ) : (
                        <Card>
                            <BlockStack gap="400">
                                <InlineStack gap="300">
                                    <Button onClick={deleteTable}>
                                        Delete Table
                                    </Button>
                                    <Button
                                        onClick={() =>
                                            addRow(
                                                tableData.length - 1,
                                                "after"
                                            )
                                        }
                                    >
                                        Add Row
                                    </Button>
                                    <Button
                                        onClick={() =>
                                            addColumn(
                                                tableData[0].cells.length - 1,
                                                "after"
                                            )
                                        }
                                    >
                                        Add Column
                                    </Button>
                                </InlineStack>
                                <DataTable
                                    columnContentTypes={Array(
                                        tableData[0]?.cells.length
                                    ).fill("text")}
                                    headings={Array(
                                        tableData[0]?.cells.length
                                    ).fill("")}
                                    rows={tableData.map((row, rowIndex) =>
                                        row.cells.map((cell, colIndex) =>
                                            renderCell(rowIndex, colIndex, cell)
                                        )
                                    )}
                                />
                            </BlockStack>
                        </Card>
                    )}
                </Layout.Section>
                <Layout.Section>
                    <Card>
                        <BlockStack gap="400">
                            <Text as="h3" variant="headingMd">
                                JSON Output
                            </Text>
                            <Box
                                as="pre"
                                padding="400"
                                background="bg-surface-secondary"
                            >
                                {generateJSON()}
                            </Box>
                        </BlockStack>
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
