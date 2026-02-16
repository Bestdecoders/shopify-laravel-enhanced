<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Shopify Laravel Enhanced</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007cba;
        }
        .search-box {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-select {
            flex: 1;
            min-width: 150px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .faq-category {
            margin-bottom: 30px;
        }
        .faq-category-header {
            background-color: #007cba;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-category-content {
            background-color: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            padding: 15px;
            display: none;
        }
        .faq-category-content.open {
            display: block;
        }
        .faq-item {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #fafafa;
        }
        .faq-question {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 8px;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-answer {
            color: #666;
            padding-top: 10px;
            display: none;
        }
        .faq-answer.open {
            display: block;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
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
        .expand-collapse-all {
            text-align: right;
            margin-bottom: 10px;
        }
        .expand-collapse-btn {
            background: none;
            border: none;
            color: #007cba;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Frequently Asked Questions</h1>
        <p>Find answers to common questions about Shopify Laravel Enhanced</p>
    </div>

    <input type="text" id="searchInput" class="search-box" placeholder="Search FAQs...">
    
    <div class="filters">
        <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <!-- Categories will be populated by JavaScript -->
        </select>
        <select id="tagFilter" class="filter-select">
            <option value="">All Tags</option>
            <!-- Tags will be populated by JavaScript -->
        </select>
    </div>

    <div id="faq-container">
        <!-- FAQ items will be populated by JavaScript -->
    </div>

    <a href="{{ url('/') }}" class="back-link">← Back to Home</a>

    <script>
        let allFaqs = [];
        let categories = [];
        let tags = [];

        // Fetch FAQ data from the API endpoint
        document.addEventListener('DOMContentLoaded', function() {
            fetch(window.location.origin + '/api/faq-data')
                .then(response => response.json())
                .then(data => {
                    allFaqs = data.faqs || [];
                    categories = data.categories || [];
                    tags = data.tags || [];
                    
                    populateFilters();
                    displayFaqs(allFaqs);
                })
                .catch(error => {
                    console.error('Error fetching FAQs:', error);
                    document.getElementById('faq-container').innerHTML = '<div class="no-results">Error loading FAQs. Please try again later.</div>';
                });
        });

        function populateFilters() {
            const categorySelect = document.getElementById('categoryFilter');
            const tagSelect = document.getElementById('tagFilter');
            
            // Populate categories
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
            
            // Populate tags
            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag;
                option.textContent = tag;
                tagSelect.appendChild(option);
            });
            
            // Add event listeners to filters
            document.getElementById('searchInput').addEventListener('input', filterFaqs);
            document.getElementById('categoryFilter').addEventListener('change', filterFaqs);
            document.getElementById('tagFilter').addEventListener('change', filterFaqs);
        }

        function filterFaqs() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const selectedCategory = document.getElementById('categoryFilter').value;
            const selectedTag = document.getElementById('tagFilter').value;
            
            let filteredFaqs = allFaqs.filter(faq => {
                const matchesSearch = !searchTerm || 
                    faq.question.toLowerCase().includes(searchTerm) || 
                    faq.answer.toLowerCase().includes(searchTerm) ||
                    faq.tags.some(tag => tag.toLowerCase().includes(searchTerm));
                
                const matchesCategory = !selectedCategory || faq.category === selectedCategory;
                const matchesTag = !selectedTag || faq.tags.includes(selectedTag);
                
                return matchesSearch && matchesCategory && matchesTag;
            });
            
            displayFaqs(filteredFaqs);
        }

        function displayFaqs(faqs) {
            const container = document.getElementById('faq-container');
            
            if (faqs.length === 0) {
                container.innerHTML = '<div class="no-results">No FAQs match your search criteria.</div>';
                return;
            }
            
            // Group FAQs by category
            const groupedFaqs = {};
            faqs.forEach(faq => {
                const category = faq.category || 'Uncategorized';
                if (!groupedFaqs[category]) {
                    groupedFaqs[category] = [];
                }
                groupedFaqs[category].push(faq);
            });
            
            let html = '';
            
            Object.keys(groupedFaqs).forEach(category => {
                html += `
                    <div class="faq-category">
                        <div class="faq-category-header" onclick="toggleCategory('${category}')">
                            <span>${category}</span>
                            <span>▼</span>
                        </div>
                        <div class="faq-category-content" id="category-${category}">
                            <div class="expand-collapse-all">
                                <button class="expand-collapse-btn" onclick="toggleAllInCategory('${category}', true)">Expand All</button>
                                <button class="expand-collapse-btn" onclick="toggleAllInCategory('${category}', false)">Collapse All</button>
                            </div>
                `;
                
                groupedFaqs[category].forEach((faq, index) => {
                    html += `
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleAnswer(this)">
                                <span>${faq.question}</span>
                                <span>+</span>
                            </div>
                            <div class="faq-answer" id="answer-${faq.id || index}">
                                ${faq.answer}
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function toggleCategory(categoryId) {
            const content = document.getElementById(`category-${categoryId}`);
            const header = content.previousElementSibling;
            const icon = header.querySelector('span:last-child');
            
            if (content.classList.contains('open')) {
                content.classList.remove('open');
                icon.textContent = '▼';
            } else {
                content.classList.add('open');
                icon.textContent = '▲';
            }
        }

        function toggleAnswer(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('span:last-child');
            
            if (answer.classList.contains('open')) {
                answer.classList.remove('open');
                icon.textContent = '+';
            } else {
                answer.classList.add('open');
                icon.textContent = '−';
            }
        }

        function toggleAllInCategory(categoryId, expand) {
            const categoryContent = document.getElementById(`category-${categoryId}`);
            const answers = categoryContent.querySelectorAll('.faq-answer');
            const icons = categoryContent.querySelectorAll('.faq-question span:last-child');
            
            answers.forEach((answer, index) => {
                if (expand) {
                    answer.classList.add('open');
                    icons[index].textContent = '−';
                } else {
                    answer.classList.remove('open');
                    icons[index].textContent = '+';
                }
            });
        }
    </script>
</body>
</html>