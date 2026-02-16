<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - Shopify Laravel Enhanced</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sidebar {
            width: 300px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            height: calc(100vh - 40px);
            position: fixed;
            overflow-y: auto;
        }
        .main-content {
            flex: 1;
            margin-left: 300px;
            padding: 30px;
            background-color: white;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #007cba;
        }
        .search-box {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .doc-nav {
            list-style: none;
            padding: 0;
        }
        .doc-nav li {
            margin-bottom: 5px;
        }
        .doc-nav a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 8px 12px;
            display: block;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .doc-nav a:hover, .doc-nav a.active {
            background-color: #34495e;
        }
        .doc-content {
            max-width: 800px;
        }
        .doc-section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #fafafa;
        }
        .doc-content h1, .doc-content h2, .doc-content h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .doc-content h1 {
            font-size: 1.8em;
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        .doc-content h2 {
            font-size: 1.5em;
            color: #007cba;
        }
        .doc-content h3 {
            font-size: 1.3em;
        }
        .toc {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #007cba;
        }
        .toc ul {
            padding-left: 20px;
        }
        .toc li {
            margin-bottom: 8px;
        }
        .toc a {
            text-decoration: none;
            color: #007cba;
        }
        .toc a:hover {
            text-decoration: underline;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-link:hover {
            background-color: #005a87;
        }
        pre {
            background-color: #f4f4f4;
            padding: 15px;
            overflow-x: auto;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .doc-category {
            margin-bottom: 20px;
        }
        .doc-category-title {
            font-size: 1.2em;
            color: #007cba;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Documentation</h2>
            <input type="text" id="searchInput" class="search-box" placeholder="Search docs...">
            
            <ul class="doc-nav" id="doc-nav">
                <!-- Navigation will be populated by JavaScript -->
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Documentation</h1>
                <p>Learn how to use Shopify Laravel Enhanced</p>
            </div>

            <div class="doc-content" id="doc-content">
                <div class="no-results" id="loading-message">
                    Select a document from the sidebar or search for topics...
                </div>
            </div>

            <a href="{{ url('/') }}" class="back-link">← Back to Home</a>
        </div>
    </div>

    <script>
        let allDocs = [];
        let categories = [];
        let currentDoc = null;

        // Fetch documentation data from the API endpoint
        document.addEventListener('DOMContentLoaded', function() {
            fetch(window.location.origin + '/api/faq-data') // Using the same API that returns docs info
                .then(response => response.json())
                .then(data => {
                    // Since the API returns FAQs, we need to fetch docs separately
                    // For now, let's fetch the docs index differently
                    loadDocsIndex();
                })
                .catch(error => {
                    console.error('Error fetching documentation:', error);
                    loadDocsIndex(); // Try to load even if there's an error
                });
        });

        function loadDocsIndex() {
            // For now, we'll simulate the docs index since the API might not return it
            // In a real scenario, we'd have a separate API endpoint for docs index
            fetch(`${window.location.origin}/api/doc-data/getting-started`)
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        // If specific doc doesn't exist, create a default structure
                        return Promise.resolve({
                            allDocs: [
                                { id: 'getting-started', title: 'Getting Started', category: 'Introduction' },
                                { id: 'installation', title: 'Installation', category: 'Setup' },
                                { id: 'configuration', title: 'Configuration', category: 'Setup' },
                                { id: 'features', title: 'Features', category: 'Core Concepts' },
                                { id: 'api-reference', title: 'API Reference', category: 'Development' },
                                { id: 'troubleshooting', title: 'Troubleshooting', category: 'Support' }
                            ],
                            categories: ['Introduction', 'Setup', 'Core Concepts', 'Development', 'Support']
                        });
                    }
                })
                .then(data => {
                    // This is a fallback - we'll fetch the actual docs index differently
                    createDefaultNavigation();
                })
                .catch(() => {
                    createDefaultNavigation();
                });
        }

        function createDefaultNavigation() {
            // Create a default navigation structure
            const defaultDocs = [
                { id: 'getting-started', title: 'Getting Started', category: 'Introduction' },
                { id: 'installation', title: 'Installation', category: 'Setup' },
                { id: 'configuration', title: 'Configuration', category: 'Setup' },
                { id: 'features', title: 'Features', category: 'Core Concepts' },
                { id: 'api-reference', title: 'API Reference', category: 'Development' },
                { id: 'troubleshooting', title: 'Troubleshooting', category: 'Support' }
            ];
            
            renderNavigation(defaultDocs);
        }

        function renderNavigation(docs) {
            allDocs = docs;
            
            // Group docs by category
            const groupedDocs = {};
            docs.forEach(doc => {
                const category = doc.category || 'General';
                if (!groupedDocs[category]) {
                    groupedDocs[category] = [];
                }
                groupedDocs[category].push(doc);
            });
            
            const navContainer = document.getElementById('doc-nav');
            navContainer.innerHTML = '';
            
            Object.keys(groupedDocs).sort().forEach(category => {
                const categoryHeader = document.createElement('li');
                categoryHeader.className = 'doc-category';
                categoryHeader.innerHTML = `<h3 class="doc-category-title">${category}</h3>`;
                navContainer.appendChild(categoryHeader);
                
                groupedDocs[category].forEach(doc => {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = '#';
                    link.textContent = doc.title;
                    link.onclick = (e) => {
                        e.preventDefault();
                        loadDocument(doc.id);
                        // Update active state
                        document.querySelectorAll('#doc-nav a').forEach(a => a.classList.remove('active'));
                        link.classList.add('active');
                    };
                    li.appendChild(link);
                    navContainer.appendChild(li);
                });
            });
            
            // Load the first document by default
            if (docs.length > 0) {
                loadDocument(docs[0].id);
                document.querySelector('#doc-nav a').classList.add('active');
            }
        }

        function loadDocument(slug) {
            fetch(`${window.location.origin}/api/doc-data/${encodeURIComponent(slug)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        displayDocument(data);
                    } else {
                        // If specific document doesn't exist, show a default page
                        document.getElementById('doc-content').innerHTML = `
                            <div class="doc-section">
                                <h1>${slug.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h1>
                                <p>This document is coming soon. Check back later for detailed information about this topic.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading document:', error);
                    document.getElementById('doc-content').innerHTML = `
                        <div class="no-results">
                            <h3>Document not found</h3>
                            <p>The requested document "${slug}" could not be loaded.</p>
                        </div>
                    `;
                });
        }

        function displayDocument(data) {
            document.getElementById('doc-content').innerHTML = `
                <div class="doc-section">
                    <h1>${data.doc?.title || data.initialDoc?.title || 'Documentation'}</h1>
                    ${data.content ? `<div class="doc-body">${data.content}</div>` : '<p>Content not available.</p>'}
                </div>
            `;
            
            // Convert markdown-like content to HTML if needed
            const contentDiv = document.querySelector('.doc-body');
            if (contentDiv) {
                // Simple conversion for common markdown elements
                let html = contentDiv.innerHTML;
                
                // Convert headers
                html = html.replace(/^### (.*$)/gm, '<h3>$1</h3>');
                html = html.replace(/^## (.*$)/gm, '<h2>$1</h2>');
                html = html.replace(/^# (.*$)/gm, '<h1>$1</h1>');
                
                // Convert bold
                html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Convert italic
                html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
                
                // Convert links
                html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
                
                // Convert paragraphs
                html = html.replace(/^\s*(.*?)(?=\n\s*\n|\n\s*$)/gm, '<p>$1</p>');
                
                contentDiv.innerHTML = html;
            }
        }

        // Add search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            if (!searchTerm) {
                // Reset navigation to show all items
                renderNavigation(allDocs);
                return;
            }
            
            const filteredDocs = allDocs.filter(doc => 
                doc.title.toLowerCase().includes(searchTerm) || 
                doc.category.toLowerCase().includes(searchTerm)
            );
            
            renderNavigation(filteredDocs);
            
            if (filteredDocs.length === 0) {
                document.getElementById('doc-nav').innerHTML = '<li><p>No documents match your search.</p></li>';
            }
        });
    </script>
</body>
</html>