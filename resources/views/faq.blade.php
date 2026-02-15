<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Shopify Laravel Enhanced</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .faq-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .faq-question {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 8px;
            color: #333;
        }
        .faq-answer {
            color: #666;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Frequently Asked Questions</h1>
        <p>Find answers to common questions about Shopify Laravel Enhanced</p>
    </div>

    <div id="faq-container">
        <!-- FAQ items will be populated by JavaScript -->
    </div>

    <a href="{{ url('/') }}" class="back-link">← Back to Home</a>

    <script>
        // Fetch FAQ data from the API endpoint
        document.addEventListener('DOMContentLoaded', function() {
            // Get the base URL for the current site
            const baseUrl = window.location.origin;
            fetch(baseUrl + '{{ route("public.faq.api") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('faq-container');

                    if (data.faqs && data.faqs.length > 0) {
                        data.faqs.forEach(faq => {
                            const faqItem = document.createElement('div');
                            faqItem.className = 'faq-item';

                            faqItem.innerHTML = `
                                <div class="faq-question">${faq.question}</div>
                                <div class="faq-answer">${faq.answer}</div>
                            `;

                            container.appendChild(faqItem);
                        });
                    } else {
                        container.innerHTML = '<p>No FAQs available at this time.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching FAQs:', error);
                    document.getElementById('faq-container').innerHTML = '<p>Error loading FAQs. Please try again later.</p>';
                });
        });
    </script>
</body>
</html>