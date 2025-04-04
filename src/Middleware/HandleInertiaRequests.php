<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            // Shared data (optional)
        ]);
    }

    public function rootView(Request $request)
    {
        return 'app'; // The root view template
    }
}
