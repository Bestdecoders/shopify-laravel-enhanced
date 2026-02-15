<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    private $faqDataPath;

    public function __construct()
    {
        // FAQ data should be published to project storage
        $this->faqDataPath = storage_path('app/faq.json');
    }

    /**
     * Display FAQ page
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getFaqs($request);
        }

        return view('shopify-enhanced::faq');
    }

    /**
     * Get FAQs with filtering and searching
     */
    public function getFaqs(Request $request)
    {
        $faqs = $this->loadFaqData(true); // Only published FAQs
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $faqs = array_filter($faqs, function ($faq) use ($searchTerm) {
                return str_contains(strtolower($faq['question']), $searchTerm) ||
                       str_contains(strtolower($faq['answer']), $searchTerm) ||
                       str_contains(strtolower(implode(' ', $faq['tags'])), $searchTerm);
            });
        }

        // Apply category filter
        if ($request->has('category') && !empty($request->category)) {
            $faqs = array_filter($faqs, function ($faq) use ($request) {
                return $faq['category'] === $request->category;
            });
        }

        // Apply tag filter
        if ($request->has('tag') && !empty($request->tag)) {
            $faqs = array_filter($faqs, function ($faq) use ($request) {
                return in_array($request->tag, $faq['tags']);
            });
        }

        // Apply priority filter
        if ($request->has('priority') && !empty($request->priority)) {
            $faqs = array_filter($faqs, function ($faq) use ($request) {
                return $faq['priority'] === $request->priority;
            });
        }

        // Sort by priority (high -> medium -> low)
        usort($faqs, function ($a, $b) {
            $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
        });

        // Re-index array to ensure proper JSON encoding
        $faqs = array_values($faqs);

        return response()->json([
            'faqs' => $faqs,
            'total' => count($faqs),
            'categories' => $this->getCategories(),
            'tags' => $this->getAllTags()
        ]);
    }

    /**
     * Get single FAQ by ID
     */
    public function show($id)
    {
        $faqs = $this->loadFaqData(true);
        $faq = collect($faqs)->firstWhere('id', (int) $id);

        if (!$faq) {
            return response()->json(['error' => 'FAQ not found'], 404);
        }

        return response()->json(['faq' => $faq]);
    }

    /**
     * Load FAQ data from JSON file
     */
    private function loadFaqData($publishedOnly = true)
    {
        if (!File::exists($this->faqDataPath)) {
            throw new \Exception('FAQ data file not found. Please publish FAQ assets using: php artisan vendor:publish --tag=shopify-enhanced-faq');
        }

        $data = json_decode(File::get($this->faqDataPath), true);
        
        if (!$data) {
            throw new \Exception('Invalid FAQ data file. Please republish FAQ assets.');
        }
        
        if ($publishedOnly) {
            $data = array_filter($data, function ($faq) {
                return isset($faq['published']) && $faq['published'] === true;
            });
        }

        return $data;
    }

    /**
     * Get all unique categories
     */
    private function getCategories()
    {
        $faqs = $this->loadFaqData(true);
        $categories = array_unique(array_column($faqs, 'category'));
        sort($categories);
        return $categories;
    }

    /**
     * Get all unique tags
     */
    private function getAllTags()
    {
        $faqs = $this->loadFaqData(true);
        $allTags = [];
        
        foreach ($faqs as $faq) {
            if (isset($faq['tags']) && is_array($faq['tags'])) {
                $allTags = array_merge($allTags, $faq['tags']);
            }
        }
        
        $uniqueTags = array_unique($allTags);
        sort($uniqueTags);
        return $uniqueTags;
    }

    /**
     * Search FAQs (dedicated endpoint)
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        return $this->getFaqs($request->merge(['search' => $request->q]));
    }
}