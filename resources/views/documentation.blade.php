<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - Shopify Laravel Enhanced</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .doc-content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .doc-content h1, .doc-content h2, .doc-content h3 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .doc-content h1 {
            font-size: 1.8em;
        }
        .doc-content h2 {
            font-size: 1.5em;
        }
        .doc-content h3 {
            font-size: 1.3em;
        }
        .toc {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
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
            padding: 10px;
            overflow-x: auto;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Documentation</h1>
        <p>Learn how to use Shopify Laravel Enhanced</p>
    </div>

    <div class="toc" id="table-of-contents">
        <h3>Table of Contents</h3>
        <ul id="toc-list">
            <!-- TOC items will be populated by JavaScript -->
        </ul>
    </div>

    <div class="doc-content" id="doc-content">
        <p>Loading documentation...</p>
    </div>

    <a href="{{ url('/') }}" class="back-link">← Back to Home</a>

    <script>
        // Get the document slug from URL or default to 'getting-started'
        const urlParams = new URLSearchParams(window.location.search);
        const docSlug = urlParams.get('doc') || 'getting-started';

        // Fetch documentation content from the API endpoint
        document.addEventListener('DOMContentLoaded', function() {
            // Get the base URL for the current site
            const baseUrl = window.location.origin;
            fetch(baseUrl + '{{ route("public.docs.api", ["slug" => "__SLUG__"]) }}'.replace('__SLUG__', docSlug))
                .then(response => response.json())
                .then(data => {
                    const contentDiv = document.getElementById('doc-content');

                    if (data.content) {
                        contentDiv.innerHTML = data.content;

                        // Generate table of contents from headings
                        generateTOC(contentDiv);
                    } else {
                        contentDiv.innerHTML = '<p>Documentation not found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching documentation:', error);
                    document.getElementById('doc-content').innerHTML = '<p>Error loading documentation. Please try again later.</p>';
                });
        });

        function generateTOC(contentDiv) {
            const headings = contentDiv.querySelectorAll('h1, h2, h3');
            const tocList = document.getElementById('toc-list');

            if (headings.length === 0) {
                tocList.innerHTML = '<li>No sections found</li>';
                return;
            }

            tocList.innerHTML = ''; // Clear existing TOC

            headings.forEach((heading, index) => {
                // Create anchor ID if it doesn't exist
                if (!heading.id) {
                    heading.id = heading.textContent.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                }

                const listItem = document.createElement('li');
                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.textContent = heading.textContent;

                // Add indentation based on heading level
                if (heading.tagName === 'H2') {
                    listItem.style.marginLeft = '20px';
                } else if (heading.tagName === 'H3') {
                    listItem.style.marginLeft = '40px';
                }

                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById(heading.id).scrollIntoView({ behavior: 'smooth' });
                });

                listItem.appendChild(link);
                tocList.appendChild(listItem);
            });
        }
    </script>
</body>
</html>