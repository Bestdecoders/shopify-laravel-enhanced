<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Illuminate\Support\Facades\Log;

class ScopeValidationService
{
    public function validateProductFilterScopes($user): array
    {
        $result = [
            'has_required_scopes' => false,
            'has_inventory_scope' => false,
            'missing_scopes' => [],
            'user_scopes' => [],
            'feature_enabled' => $this->isProductFilterEnabled(),
        ];

        if (!$this->isProductFilterEnabled()) {
            $result['message'] = 'Product filter feature is disabled';
            return $result;
        }

        // Get user scopes from kyon package or user model
        $userScopesString = config('shopify-app.api_scopes');

        if (!$userScopesString) {
            $result['message'] = 'User has no scopes';
            return $result;
        }

        $userScopes = $this->parseScopes($userScopesString);
        $result['user_scopes'] = $userScopes;

        // Check required scopes
        $requiredScopes = config('shopify-enhanced.product_filter.required_scopes', ['read_products']);
        $hasRequiredScopes = $this->hasAnyRequiredScope($userScopes, $requiredScopes);

        if (!$hasRequiredScopes) {
            $result['missing_scopes'] = array_diff($requiredScopes, $userScopes);
            $result['message'] = 'Missing required scopes: ' . implode(', ', $result['missing_scopes']);
        } else {
            $result['has_required_scopes'] = true;
            $result['message'] = 'All required scopes available';
        }

        // Check optional inventory scope
        $inventoryScopes = config('shopify-enhanced.product_filter.optional_scopes', ['read_inventory']);
        $result['has_inventory_scope'] = $this->hasAnyScope($userScopes, $inventoryScopes);

        return $result;
    }

    public function hasProductFilterAccess($user): bool
    {
        if (!$this->isProductFilterEnabled()) {
            return false;
        }

        $validation = $this->validateProductFilterScopes($user);
        return $validation['has_required_scopes'];
    }

    public function canIncludeInventoryData($user): bool
    {
        if (!$this->isProductFilterEnabled()) {
            return false;
        }

        if (!config('shopify-enhanced.product_filter.include_inventory_data', false)) {
            return false;
        }

        $validation = $this->validateProductFilterScopes($user);
        return $validation['has_inventory_scope'];
    }

    public function getRecommendedScopes(): array
    {
        $scopes = config('shopify-enhanced.product_filter.required_scopes', ['read_products']);

        if (config('shopify-enhanced.product_filter.include_inventory_data', false)) {
            $optionalScopes = config('shopify-enhanced.product_filter.optional_scopes', ['read_inventory']);
            $scopes = array_merge($scopes, $optionalScopes);
        }

        return array_unique($scopes);
    }

    public function logScopeValidation($user, string $context = 'general'): void
    {
        if (!$this->isProductFilterEnabled()) {
            return;
        }

        $validation = $this->validateProductFilterScopes($user);

        Log::info("Product filter scope validation", [
            'context' => $context,
            'user_id' => $user->id ?? null,
            'shop_domain' => $user->name ?? null,
            'has_required_scopes' => $validation['has_required_scopes'],
            'has_inventory_scope' => $validation['has_inventory_scope'],
            'user_scopes' => $validation['user_scopes'],
            'missing_scopes' => $validation['missing_scopes'],
            'message' => $validation['message'],
        ]);
    }

    protected function parseScopes($scopeString): array
    {
        if (empty($scopeString)) {
            return [];
        }

        return array_map('trim', explode(',', $scopeString));
    }

    protected function hasAnyRequiredScope(array $userScopes, array $requiredScopes): bool
    {
        // Check if user has any of the required scopes or write_products (which includes read_products)
        foreach ($requiredScopes as $scope) {
            if (in_array($scope, $userScopes)) {
                return true;
            }

            // If required scope is read_products, check for write_products
            if ($scope === 'read_products' && in_array('write_products', $userScopes)) {
                return true;
            }

            // If required scope is read_inventory, check for write_inventory
            if ($scope === 'read_inventory' && in_array('write_inventory', $userScopes)) {
                return true;
            }
        }

        return false;
    }

    protected function hasAnyScope(array $userScopes, array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (in_array($scope, $userScopes)) {
                return true;
            }

            // Check for write equivalent
            $writeScope = str_replace('read_', 'write_', $scope);
            if (in_array($writeScope, $userScopes)) {
                return true;
            }
        }

        return false;
    }

    protected function isProductFilterEnabled(): bool
    {
        return config('shopify-enhanced.product_filter.enabled', false);
    }
}