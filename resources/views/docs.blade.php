<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - Shopify Laravel Enhanced</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        /* Markdown content styling */
        .markdown-content h1 { font-size: 2em; font-weight: bold; margin: 1em 0 0.5em 0; color: #111827; }
        .markdown-content h2 { font-size: 1.5em; font-weight: bold; margin: 1em 0 0.5em 0; color: #111827; }
        .markdown-content h3 { font-size: 1.25em; font-weight: bold; margin: 1em 0 0.5em 0; color: #111827; }
        .markdown-content h4 { font-size: 1.1em; font-weight: bold; margin: 1em 0 0.5em 0; color: #111827; }
        .markdown-content p { margin: 0.75em 0; line-height: 1.7; color: #4b5563; }
        .markdown-content ul, .markdown-content ol { margin: 0.75em 0; padding-left: 1.5em; }
        .markdown-content li { margin: 0.25em 0; color: #4b5563; }
        .markdown-content code { background: #f3f4f6; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.9em; color: #e11d48; }
        .markdown-content pre { background: #1f2937; color: #f9fafb; padding: 1em; border-radius: 8px; overflow-x: auto; margin: 1em 0; font-size: 0.875em; white-space: pre; }
        .markdown-content pre code { background: transparent; color: #f9fafb; padding: 0; white-space: pre; }
        @media (max-width: 768px) {
            .markdown-content pre { font-size: 0.75em !important; padding: 0.75em; white-space: pre-wrap; word-break: break-word; }
            .markdown-content pre code { font-size: 0.75em !important; white-space: pre-wrap; word-break: break-word; }
            .markdown-content code { font-size: 0.85em; word-break: break-word; }
        }
        .markdown-content a { color: #4f46e5; text-decoration: underline; }
        .markdown-content a:hover { color: #4338ca; }
        .markdown-content blockquote { border-left: 4px solid #4f46e5; padding-left: 1em; margin: 1em 0; color: #6b7280; font-style: italic; }
        .markdown-content table { width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 0.9em; }
        .markdown-content th, .markdown-content td { border: 1px solid #e5e7eb; padding: 0.5em; text-align: left; }
        .markdown-content th { background: #f9fafb; font-weight: bold; }
        .markdown-content hr { border: none; border-top: 1px solid #e5e7eb; margin: 2em 0; }
        .markdown-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1em 0; }
        .markdown-content strong { font-weight: bold; color: #111827; }
        .markdown-content em { font-style: italic; }
        @media (max-width: 768px) {
            .markdown-content { font-size: 0.9em; }
            .markdown-content h1 { font-size: 1.5em; }
            .markdown-content h2 { font-size: 1.25em; }
            .markdown-content h3 { font-size: 1.1em; }
            .markdown-content table { display: block; overflow-x: auto; white-space: nowrap; font-size: 0.8em; }
            .markdown-content th, .markdown-content td { padding: 0.4em; font-size: 0.8em; }
            .markdown-content ul, .markdown-content ol { padding-left: 1.2em; }
            .markdown-content blockquote { font-size: 0.9em; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-6">
            <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
            <h1 class="text-3xl font-bold text-gray-900" id="page-title">Documentation</h1>
            <p class="text-gray-600 mt-2" id="page-description">Complete guide to using Shopify Laravel Enhanced.</p>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:w-64 flex-shrink-0">
                <nav class="lg:sticky lg:top-8 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="font-semibold text-gray-900 mb-4 px-2">Topics</h2>
                    <ul class="space-y-1" id="docs-nav">
                        @foreach(isset($docsIndex) ? $docsIndex : [] as $section)
                        <li class="mb-4">
                            <div class="px-2 py-1 text-xs font-bold text-gray-900 uppercase tracking-wider">
                                {{ $section['title'] ?? 'Section' }}
                            </div>
                            @if(isset($section['pages']) && is_array($section['pages']))
                            <ul class="space-y-1 mt-1">
                                @foreach($section['pages'] as $page)
                                <li>
                                    <a href="javascript:void(0)"
                                       data-slug="{{ $page['slug'] ?? '' }}"
                                       data-title="{{ $page['title'] ?? '' }}"
                                       class="doc-link block px-2 py-1.5 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-indigo-600 transition cursor-pointer">
                                        {{ $page['title'] ?? 'Untitled' }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Index View -->
                <div id="index-view" class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    @forelse(isset($docsIndex) ? $docsIndex : [] as $section)
                        <div class="mb-8 last:mb-0">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                {{ $section['title'] ?? 'Section' }}
                            </h2>

                            @if(isset($section['pages']) && is_array($section['pages']))
                            <div class="grid gap-4">
                                @foreach($section['pages'] as $page)
                                <a href="javascript:void(0)"
                                   data-slug="{{ $page['slug'] ?? '' }}"
                                   data-title="{{ $page['title'] ?? '' }}"
                                   class="doc-card-link group flex items-start gap-4 p-4 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition cursor-pointer">
                                    <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600 transition">
                                            {{ $page['title'] ?? 'Untitled' }}
                                        </h3>
                                        @if(isset($page['description']))
                                        <p class="text-gray-600 text-sm mt-1 line-clamp-2">
                                            {{ $page['description'] }}
                                        </p>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @empty
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No documentation found</h3>
                        <p class="text-gray-600">Documentation is being updated. Check back later.</p>
                    </div>
                    @endforelse
                </div>

                <!-- Single Doc View (hidden by default) -->
                <div id="doc-view" class="hidden relative">
                    <!-- TOC Toggle Button -->
                    <button id="toc-toggle" class="fixed right-0 top-1/2 -translate-y-1/2 z-50 bg-indigo-600 text-white px-2 py-4 rounded-l-lg shadow-lg hover:bg-indigo-700 transition" style="writing-mode: vertical-rl; text-orientation: mixed;">
                        Table of Contents
                    </button>

                    <!-- TOC Close Button (hidden by default) -->
                    <button id="toc-close" class="fixed right-0 top-1/2 -translate-y-1/2 -translate-y-32 z-50 bg-red-500 text-white px-2 py-4 rounded-l-lg shadow-lg hover:bg-red-600 transition hidden" style="writing-mode: vertical-rl; text-orientation: mixed;">
                        ✕
                    </button>

                    <!-- TOC Sidebar -->
                    <div id="toc-sidebar" class="fixed right-0 top-0 h-full w-80 bg-white shadow-2xl border-l border-gray-200 transform translate-x-full transition-transform duration-300 z-40 overflow-y-auto">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-6">Table of Contents</h3>
                            <ul class="space-y-2" id="doc-toc-list"></ul>
                        </div>
                    </div>

                    <!-- Overlay -->
                    <div id="toc-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

                    <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                        <div id="doc-content" class="markdown-content"></div>
                    </article>

                    <!-- Navigation -->
                    <div id="doc-nav-buttons" class="mt-8 flex justify-between gap-4 hidden"></div>
                </div>

                <!-- Link to FAQ -->
                <div class="mt-8 bg-gray-100 rounded-lg p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Have questions?</h3>
                    <p class="text-gray-600 mb-4">Check our FAQ for quick answers to common questions.</p>
                    <a href="{{ url('/faq') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 transition">
                        View FAQ
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-6">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Shopify Laravel Enhanced. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Configure marked.js
        if (typeof marked !== 'undefined') {
            marked.setOptions({
                breaks: true,
                gfm: true
            });
        }

        // Flatten docs index
        let flatDocs = [];
        @php
            $flatDocs = [];
            foreach(isset($docsIndex) ? $docsIndex : [] as $section) {
                if(isset($section['pages']) && is_array($section['pages'])) {
                    foreach($section['pages'] as $page) {
                        $flatDocs[] = $page;
                    }
                }
            }
        @endphp
        flatDocs = {!! json_encode($flatDocs) !!};

        // Initialize after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Load first doc automatically
            if (flatDocs.length > 0) {
                loadDoc(flatDocs[0].slug);
            }

            // Add click handlers for navigation
            document.querySelectorAll('.doc-link, .doc-card-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const slug = this.dataset.slug;
                    if (slug) {
                        loadDoc(slug);
                    }
                });
            });
        });

        async function loadDoc(slug) {
            try {
                const response = await fetch('/api/doc-data/' + encodeURIComponent(slug));
                if (!response.ok) {
                    console.error('Failed to load doc:', slug);
                    return;
                }
                const data = await response.json();

                // Update header
                if (data.doc) {
                    document.getElementById('page-title').textContent = data.doc.title;
                    document.getElementById('page-description').textContent = data.doc.description || '';
                    document.title = data.doc.title + ' - Shopify Laravel Enhanced';
                }

                // Update active nav item
                document.querySelectorAll('.doc-link').forEach(link => {
                    if (link.dataset.slug === slug) {
                        link.classList.add('bg-indigo-50', 'text-indigo-600', 'font-medium');
                        link.classList.remove('text-gray-700');
                    } else {
                        link.classList.remove('bg-indigo-50', 'text-indigo-600', 'font-medium');
                        link.classList.add('text-gray-700');
                    }
                });

                // Show content - parse markdown using marked.js
                const contentDiv = document.getElementById('doc-content');
                if (data.content) {
                    if (typeof marked !== 'undefined') {
                        contentDiv.innerHTML = marked.parse(data.content);

                        // Add IDs to all headings
                        const headings = contentDiv.querySelectorAll('h1, h2, h3, h4, h5, h6');
                        headings.forEach((heading, index) => {
                            heading.id = 'heading-' + index;
                        });

                        // Show TOC if available
                        if (data.toc && data.toc.length > 0) {
                            const tocListContainer = document.getElementById('doc-toc-list');
                            tocListContainer.innerHTML = '';

                            // Match TOC items to headings by iterating through both
                            let tocIndex = 0;
                            headings.forEach((heading) => {
                                // Find TOC item that matches this heading
                                const headingLevel = parseInt(heading.tagName.substring(1));
                                const headingText = heading.textContent.trim();

                                // Look for matching TOC item
                                for (let i = tocIndex; i < data.toc.length; i++) {
                                    const tocItem = data.toc[i];
                                    if (tocItem.level === headingLevel) {
                                        // Found matching TOC item - create link
                                        const li = document.createElement('li');
                                        const a = document.createElement('a');
                                        a.href = '#' + heading.id;
                                        a.dataset.targetId = heading.id;
                                        a.className = 'text-sm text-gray-600 hover:text-indigo-600 transition block py-1';
                                        a.style.paddingLeft = (8 + (tocItem.level - 1) * 16) + 'px';
                                        a.textContent = tocItem.title;
                                        a.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            const target = document.getElementById(this.dataset.targetId);
                                            if (target) {
                                                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                            }
                                        });
                                        li.appendChild(a);
                                        tocListContainer.appendChild(li);

                                        tocIndex = i + 1;
                                        break;
                                    }
                                }
                            });

                            // Show TOC button
                            document.getElementById('toc-toggle').classList.remove('hidden');
                        } else {
                            document.getElementById('toc-toggle').classList.add('hidden');
                        }

                        // Handle broken images
                        contentDiv.querySelectorAll('img').forEach(img => {
                            img.onerror = function() {
                                this.style.display = 'none';
                            };
                        });
                    } else {
                        // Fallback: show content as preformatted text
                        contentDiv.innerHTML = '<pre>' + data.content + '</pre>';
                    }
                } else {
                    contentDiv.innerHTML = '<p>Content not available.</p>';
                }

                // Update navigation
                updateDocNavigation(slug);

                // Show doc view, hide index
                document.getElementById('index-view').classList.add('hidden');
                document.getElementById('doc-view').classList.remove('hidden');

                // Scroll to top of content
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Set up TOC highlighting after content loads
                setTimeout(() => setupTocHighlighting(), 100);

            } catch (error) {
                console.error('Error loading doc:', error);
            }
        }

        function updateDocNavigation(currentSlug) {
            const currentIndex = flatDocs.findIndex(doc => doc.slug === currentSlug);
            if (currentIndex === -1) {
                document.getElementById('doc-nav-buttons').classList.add('hidden');
                return;
            }

            const navContainer = document.getElementById('doc-nav-buttons');
            navContainer.innerHTML = '';

            if (currentIndex > 0) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'nav-btn flex-1 px-6 py-4 bg-white border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition text-left';
                prevBtn.dataset.slug = flatDocs[currentIndex - 1].slug;

                const prevLabel = document.createElement('div');
                prevLabel.className = 'text-sm text-gray-500 mb-1';
                prevLabel.textContent = 'Previous';

                const prevTitle = document.createElement('div');
                prevTitle.className = 'font-medium text-gray-900';
                prevTitle.textContent = flatDocs[currentIndex - 1].title;

                prevBtn.appendChild(prevLabel);
                prevBtn.appendChild(prevTitle);
                navContainer.appendChild(prevBtn);
            } else {
                const spacer = document.createElement('div');
                spacer.className = 'flex-1';
                navContainer.appendChild(spacer);
            }

            if (currentIndex < flatDocs.length - 1) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'nav-btn flex-1 px-6 py-4 bg-white border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition text-right';
                nextBtn.dataset.slug = flatDocs[currentIndex + 1].slug;

                const nextLabel = document.createElement('div');
                nextLabel.className = 'text-sm text-gray-500 mb-1';
                nextLabel.textContent = 'Next';

                const nextTitle = document.createElement('div');
                nextTitle.className = 'font-medium text-gray-900';
                nextTitle.textContent = flatDocs[currentIndex + 1].title;

                nextBtn.appendChild(nextLabel);
                nextBtn.appendChild(nextTitle);
                navContainer.appendChild(nextBtn);
            } else {
                const spacer = document.createElement('div');
                spacer.className = 'flex-1';
                navContainer.appendChild(spacer);
            }

            document.getElementById('doc-nav-buttons').classList.remove('hidden');

            // Add click handlers to nav buttons
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const slug = this.dataset.slug;
                    if (slug) {
                        loadDoc(slug);
                    }
                });
            });
        }

        function showIndex() {
            document.getElementById('page-title').textContent = 'Documentation';
            document.getElementById('page-description').textContent = 'Complete guide to using Shopify Laravel Enhanced.';
            document.title = 'Documentation - Shopify Laravel Enhanced';

            document.querySelectorAll('.doc-link').forEach(link => {
                link.classList.remove('bg-indigo-50', 'text-indigo-600', 'font-medium');
                link.classList.add('text-gray-700');
            });

            document.getElementById('index-view').classList.remove('hidden');
            document.getElementById('doc-view').classList.add('hidden');
            document.getElementById('doc-nav-buttons').classList.add('hidden');
            document.getElementById('toc-toggle').classList.add('hidden');
        }

        // TOC Sidebar functions
        function openTocSidebar() {
            document.getElementById('toc-sidebar').classList.remove('translate-x-full');
            document.getElementById('toc-overlay').classList.remove('hidden');
            document.getElementById('toc-close').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTocSidebar() {
            document.getElementById('toc-sidebar').classList.add('translate-x-full');
            document.getElementById('toc-overlay').classList.add('hidden');
            document.getElementById('toc-close').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // TOC event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toc-toggle').addEventListener('click', openTocSidebar);
            document.getElementById('toc-close').addEventListener('click', closeTocSidebar);

            // Set up intersection observer for highlighting current TOC item
            setupTocHighlighting();
        });

        // Highlight TOC item based on scroll position
        function setupTocHighlighting() {
            const contentDiv = document.getElementById('doc-content');
            if (!contentDiv) return;

            const headings = contentDiv.querySelectorAll('h1, h2, h3, h4, h5, h6');
            const tocLinks = document.querySelectorAll('#doc-toc-list a');

            if (headings.length === 0 || tocLinks.length === 0) return;

            // Track which heading is currently at the top
            let currentActiveId = null;

            // Function to update active TOC item
            function updateActiveToc(id) {
                if (currentActiveId === id) return;
                currentActiveId = id;

                tocLinks.forEach(link => {
                    const targetId = link.getAttribute('data-target-id');
                    if (targetId === id) {
                        link.classList.remove('text-gray-600');
                        link.classList.add('bg-indigo-100', 'text-indigo-600', 'font-semibold');
                    } else {
                        link.classList.remove('bg-indigo-100', 'text-indigo-600', 'font-semibold');
                        link.classList.add('text-gray-600');
                    }
                });
            }

            // Use scroll event listener instead of Intersection Observer for more control
            function onScroll() {
                const scrollTop = window.scrollY;
                const viewportHeight = window.innerHeight;

                let closestHeading = null;
                let closestDistance = Infinity;

                headings.forEach(heading => {
                    if (!heading.id) return;

                    const rect = heading.getBoundingClientRect();
                    const headingTop = rect.top + scrollTop;

                    // Check if heading is in the viewport
                    if (rect.top >= 0 && rect.top < viewportHeight * 0.3) {
                        // Heading is in top 30% of viewport
                        if (rect.top < closestDistance) {
                            closestDistance = rect.top;
                            closestHeading = heading.id;
                        }
                    }
                });

                // If no heading in top 30%, use the first one above viewport
                if (!closestHeading) {
                    for (let i = headings.length - 1; i >= 0; i--) {
                        const heading = headings[i];
                        if (!heading.id) continue;
                        const rect = heading.getBoundingClientRect();
                        if (rect.top < 0) {
                            closestHeading = heading.id;
                            break;
                        }
                    }
                }

                if (closestHeading) {
                    updateActiveToc(closestHeading);
                }
            }

            // Use Intersection Observer as backup to detect when headings enter viewport
            const observer = new IntersectionObserver((entries) => {
                onScroll();
            }, {
                rootMargin: '-10% 0px -80% 0px',
                threshold: 0
            });

            headings.forEach(heading => {
                if (heading.id) {
                    observer.observe(heading);
                }
            });

            // Also listen to scroll events for more accurate detection
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    window.requestAnimationFrame(() => {
                        onScroll();
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        }
    </script>
</body>
</html>
