<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Shopify Laravel Enhanced</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Frequently Asked Questions</h1>
            <p class="text-gray-600 mt-2">Find answers to common questions about Shopify Laravel Enhanced.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- Category Filter -->
        @if(isset($categories) && count($categories) > 1)
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <button onclick="filterByCategory('all')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition" data-category="all">
                    All
                </button>
                @foreach($categories as $category)
                <button onclick="filterByCategory('{{ $category }}')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 transition" data-category="{{ $category }}">
                    {{ $category }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- FAQ List -->
        <div class="space-y-4">
            @forelse(isset($faqs) ? $faqs : [] as $faq)
            <div class="faq-item bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" data-category="{{ $faq['category'] ?? 'General' }}">
                <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition">
                    <span class="font-medium text-gray-900 pr-4">{{ $faq['question'] ?? '' }}</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-content hidden px-6 pb-4">
                    <div class="text-gray-600 leading-relaxed">
                        {!! $faq['answer'] ?? '' !!}
                    </div>
                    @if(isset($faq['tags']) && !empty($faq['tags']))
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($faq['tags'] as $tag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No FAQs found</h3>
                <p class="text-gray-600">Check back later for updates.</p>
            </div>
            @endforelse
        </div>

        <!-- Link to Documentation -->
        <div class="mt-12 bg-indigo-50 rounded-lg p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Need more help?</h3>
            <p class="text-gray-600 mb-4">Check out our comprehensive documentation for detailed guides.</p>
            <a href="{{ url('/docs') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                View Documentation
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Shopify Laravel Enhanced. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        function toggleFaq(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('svg');

            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        function filterByCategory(category) {
            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.category === category) {
                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                    btn.classList.add('bg-indigo-600', 'text-white');
                } else {
                    btn.classList.remove('bg-indigo-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                }
            });

            // Filter FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Open FAQ from URL hash
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash;
            if (hash) {
                const faqId = hash.substring(1);
                const faqItem = document.querySelector(`[data-id="${faqId}"]`);
                if (faqItem) {
                    const button = faqItem.querySelector('button');
                    toggleFaq(button);
                    faqItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>
</html>
