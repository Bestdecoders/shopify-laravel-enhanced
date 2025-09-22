<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Middleware;

use Bestdecoders\ShopifyLaravelEnhanced\Services\ScopeValidationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateProductFilterScopes
{
    protected ScopeValidationService $scopeValidator;

    public function __construct(ScopeValidationService $scopeValidator)
    {
        $this->scopeValidator = $scopeValidator;
    }

    public function handle(Request $request, Closure $next)
    {
        // Skip if feature is not enabled
        if (!config('shopify-enhanced.product_filter.enabled', false)) {
            return response()->json([
                'error' => 'Product filter feature is not enabled',
                'code' => 'FEATURE_DISABLED'
            ], 403);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Authentication required',
                'code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Validate scopes
        $validation = $this->scopeValidator->validateProductFilterScopes($user);

        if (!$validation['has_required_scopes']) {
            return response()->json([
                'error' => 'Insufficient Shopify app permissions',
                'message' => $validation['message'],
                'missing_scopes' => $validation['missing_scopes'],
                'required_scopes' => config('shopify-enhanced.product_filter.required_scopes', ['read_products']),
                'user_scopes' => $validation['user_scopes'],
                'code' => 'INSUFFICIENT_SCOPES'
            ], 403);
        }

        // Add scope information to request for controllers to use
        $request->attributes->set('product_filter_scopes', $validation);

        return $next($request);
    }
}

