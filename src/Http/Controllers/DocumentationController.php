<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    private $docsPath;
    private $docsIndexPath;

    public function __construct()
    {
        // Documentation files should be published to project storage
        $this->docsPath = storage_path('app/docs');
        $this->docsIndexPath = storage_path('app/docs/docs.json');
    }

    /**
     * Display documentation index page
     */
    public function index()
    {
        $docsIndex = $this->loadDocsIndex();

        return view('shopify-enhanced::documentation');
    }

    /**
     * Display single documentation page
     */
    public function show($slug)
    {
        $docsIndex = $this->loadDocsIndex();
        $doc = collect($docsIndex)->firstWhere('id', $slug);

        if (!$doc) {
            abort(404, 'Documentation not found');
        }

        $filePath = $this->docsPath . '/' . $doc['file'];
        
        if (!File::exists($filePath)) {
            throw new \Exception('Documentation file not found. Please publish documentation assets using: php artisan vendor:publish --tag=shopify-enhanced-docs');
        }

        $content = File::get($filePath);

        // Extract table of contents from markdown headers
        $toc = $this->extractTableOfContents($content);

        return inertia('Documentation', [
            'docs' => $docsIndex,
            'categories' => $this->getCategories($docsIndex),
            'initialDoc' => $doc,
            'initialContent' => $content,
            'initialToc' => $toc
        ]);
    }

    /**
     * API endpoint to get single document content
     */
    public function getDocument($slug)
    {
        $docsIndex = $this->loadDocsIndex();
        $doc = collect($docsIndex)->firstWhere('id', $slug);

        if (!$doc) {
            return response()->json(['error' => 'Documentation not found'], 404);
        }

        $filePath = $this->docsPath . '/' . $doc['file'];
        
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Documentation file not found. Please publish documentation assets.'], 404);
        }

        $content = File::get($filePath);
        $toc = $this->extractTableOfContents($content);

        return response()->json([
            'doc' => $doc,
            'content' => $content,
            'toc' => $toc,
            'allDocs' => $docsIndex,
            'categories' => $this->getCategories($docsIndex)
        ]);
    }

    /**
     * Search documentation
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $searchTerm = strtolower($request->q);
        $docsIndex = $this->loadDocsIndex();
        $results = [];

        foreach ($docsIndex as $doc) {
            $filePath = $this->docsPath . '/' . $doc['file'];
            
            if (!File::exists($filePath)) {
                continue;
            }

            $content = File::get($filePath);
            $lowerContent = strtolower($content);
            $lowerTitle = strtolower($doc['title']);
            $lowerDescription = strtolower($doc['description']);

            if (str_contains($lowerTitle, $searchTerm) || 
                str_contains($lowerDescription, $searchTerm) || 
                str_contains($lowerContent, $searchTerm)) {
                
                // Extract relevant snippet
                $snippet = $this->extractSnippet($content, $searchTerm);
                
                $results[] = array_merge($doc, [
                    'snippet' => $snippet,
                    'relevance' => $this->calculateRelevance($searchTerm, $doc, $content)
                ]);
            }
        }

        // Sort by relevance
        usort($results, function ($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        return response()->json([
            'results' => $results,
            'query' => $request->q,
            'total' => count($results)
        ]);
    }

    /**
     * Load documentation index from JSON
     */
    private function loadDocsIndex()
    {
        if (!File::exists($this->docsIndexPath)) {
            throw new \Exception('Documentation index not found. Please publish documentation assets using: php artisan vendor:publish --tag=shopify-enhanced-docs');
        }

        $data = json_decode(File::get($this->docsIndexPath), true);
        
        if (!$data) {
            throw new \Exception('Invalid documentation index file. Please republish documentation assets.');
        }

        // Filter published docs and sort by order
        $publishedDocs = array_filter($data, function ($doc) {
            return isset($doc['published']) && $doc['published'] === true;
        });

        usort($publishedDocs, function ($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });

        return $publishedDocs;
    }

    /**
     * Get unique categories from docs
     */
    private function getCategories($docs)
    {
        $categories = array_unique(array_column($docs, 'category'));
        sort($categories);
        return $categories;
    }

    /**
     * Extract table of contents from markdown headers
     */
    private function extractTableOfContents($content)
    {
        $toc = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $title = trim($matches[2]);
                $slug = Str::slug($title);

                $toc[] = [
                    'level' => $level,
                    'title' => $title,
                    'slug' => $slug
                ];
            }
        }

        return $toc;
    }

    /**
     * Extract relevant snippet for search results
     */
    private function extractSnippet($content, $searchTerm, $length = 200)
    {
        $lowerContent = strtolower($content);
        $position = strpos($lowerContent, strtolower($searchTerm));
        
        if ($position === false) {
            return substr(strip_tags($content), 0, $length) . '...';
        }

        $start = max(0, $position - 50);
        $snippet = substr($content, $start, $length);
        
        // Clean up snippet
        $snippet = strip_tags($snippet);
        $snippet = preg_replace('/^[^A-Z]*/', '', $snippet); // Remove partial words at start
        
        return '...' . $snippet . '...';
    }

    /**
     * Calculate search relevance score
     */
    private function calculateRelevance($searchTerm, $doc, $content)
    {
        $score = 0;
        $lowerTerm = strtolower($searchTerm);

        // Title match (highest weight)
        if (str_contains(strtolower($doc['title']), $lowerTerm)) {
            $score += 100;
        }

        // Description match
        if (str_contains(strtolower($doc['description']), $lowerTerm)) {
            $score += 50;
        }

        // Content matches
        $contentMatches = substr_count(strtolower($content), $lowerTerm);
        $score += $contentMatches * 10;

        // Category match
        if (str_contains(strtolower($doc['category']), $lowerTerm)) {
            $score += 25;
        }

        return $score;
    }
}