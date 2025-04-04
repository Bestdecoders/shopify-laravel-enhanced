import React, { useState, useRef } from 'react';
import JoditEditor from 'jodit-react';

const SCEditor = () => {
    const editor = useRef(null);
    const [content, setContent] = useState("");

    const config = {
        readonly: false, // Enable editing
        height: 400, // Height of the editor
        toolbarAdaptive: false, // Set toolbar to be adaptive
        removeButtons: ['speech','about'], // Remove table and paint tools
        uploader: {
            insertImageAsBase64URI: false, // Disable image upload
            url: '', // Optional: if you want to avoid uploading altogether
        },
        filebrowser: {
            buttons: ['link'], // Only allow adding images via URL
        },
        toolbarSticky: false,
        disablePlugins: ['poweredByJodit','speech-recognize','font'], 
        
    };

    return (
        <div>
            <JoditEditor
                ref={editor}
                value={content}
                config={config}
                tabIndex={1} // Tab index for the editor
                onBlur={(newContent) => setContent(newContent)} // Handle content change
                onChange={(newContent) => {}}
            />
            <div>
                <h3>Editor Content:</h3>
                <p>{content}</p> {/* Display the editor content below */}
            </div>
        </div>
    );
};

export default SCEditor;
