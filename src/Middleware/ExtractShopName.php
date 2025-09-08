<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtractShopName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if shop is already present (set by VerifyShopify middleware)
        if ($request->has('shop') && $request->input('shop')) {
            return $next($request);
        }

        // Try to get shop from session token if available
        $token = $request->input('token') ?: $request->bearerToken();
        if ($token) {
            // Decode JWT session token to extract shop domain
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode($parts[1]), true);
                if (isset($payload['dest'])) {
                    $parsedUrl = parse_url($payload['dest']);
                    if (isset($parsedUrl['host'])) {
                        $request->merge(['shop' => $parsedUrl['host']]);
                        return $next($request);
                    }
                }
            }
        }

        // Fallback: existing host parameter logic
        $host = $request->input('host');

        if (!$host) {
            Log::error('ExtractShopName: Host not provided', [
                'url' => $request->url(),
                'params' => $request->all()
            ]);
            return response()->json([
                'error' => 'Host not provided',
            ], 400);
        }

        // Decode the base64-encoded host
        $decodedHost = base64_decode($host);

        if (!$decodedHost) {
            Log::error('ExtractShopName: Host decoding failed', [
                'host' => $host,
                'url' => $request->url()
            ]);
            return response()->json([
                'error' => 'Host decoding failed',
            ], 400);
        }

        // Extract shop name from the decoded host
        if (preg_match('/store\/([^\/]+)/', $decodedHost, $matches)) {
            $shop = $matches[1] . '.myshopify.com'; // Extracted shop name

            // Add the shop name to the request object
            $request->merge(['shop' => $shop]);

            return $next($request);
        }

        Log::error('ExtractShopName: Shop not found', [
            'host' => $host,
            'decodedHost' => $decodedHost,
            'url' => $request->url()
        ]);

        return response()->json([
            'error' => 'Shop not found',
        ], 404);
    }
}
