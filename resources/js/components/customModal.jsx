import React from "react";

const CustomModal = ({ isVisible, onClose, options }) => {
    if (!isVisible) return null;

    return (
        <div className="custom-modal">
            <div className="modal-content">
                {options.map((option, index) => (
                    <button key={index} onClick={option.onAction}>
                        {option.content}
                    </button>
                ))}
            </div>

            {/* Custom styles for the modal */}
            <style jsx>{`
                .custom-modal {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    background-color: white;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    border: 1px solid #ccc;
                    padding: 16px;
                    z-index: 999999;
                    overflow: visible;
                }
                .modal-content {
                    display: flex;
                    flex-direction: column;
                }
                .modal-content button {
                    margin: 4px 0;
                    padding: 8px;
                    background-color: #007ace;
                    color: white;
                    border: none;
                    cursor: pointer;
                }
                .modal-content button:hover {
                    background-color: #005999;
                }
            `}</style>
        </div>
    );
};

export default CustomModal;
