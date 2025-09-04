<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Helpers;

class Queries
{
    private static $queries = [];

    // Initialize default queries from the config
    public static function init()
    {
        if (!empty(self::$queries)) {
            return;
        }

        // Load queries from the configuration file
        self::$queries = config('shopify-enhanced.queries', []);
    }

    // Get a query based on dot notation path
    public static function getQuery(string $path, ...$params)
    {
        self::init();

        $keys = explode('.', $path);
        $query = self::$queries;

        // Traverse the nested array using the provided keys
        foreach ($keys as $key) {
            $key = strtolower($key); // Normalize to lowercase
            if (isset($query[$key])) {
                $query = $query[$key];
            } else {
                return null; // Return null if the key is not found
            }
        }

        // If the result is callable, execute it with the given parameters
        if (is_callable($query)) {
            return call_user_func_array($query, $params);
        }

        return is_array($query) ? null : $query;
    }

    // Extend or override queries
    public static function extend(array $newQueries)
    {
        self::$queries = array_merge_recursive(self::$queries, $newQueries);
    }
}
