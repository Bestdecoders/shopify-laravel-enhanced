import React, { useState } from "react";
const EditableInput = ({ value, onChange, onBlur, setShowCellActionModal }) => {
    const [inputValue, setInputValue] = useState(value);

    const handleInputChange = (e) => {
        setInputValue(e.target.value);
    };

    const handleBlur = () => {
        onBlur(inputValue);
        // setShowCellActionModal(false);
    };

    return (
        <input
            type="text"
            value={inputValue}
            onChange={handleInputChange}
            onBlur={handleBlur}
            autoFocus
        />
    );
};

export default EditableInput;
