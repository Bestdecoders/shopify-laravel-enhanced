<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        $host = $request->input('host');

        if (!$host) {
            return response()->json([
                'error' => 'Host not provided',
            ], 400);
        }

        // Decode the base64-encoded host
        $decodedHost = base64_decode($host);

        if (!$decodedHost) {
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

        return response()->json([
            'error' => 'Shop not found',
        ], 404);
    }
}
