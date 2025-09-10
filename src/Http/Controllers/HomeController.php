<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    /**
     * Show the Bestdecoders home page
     */
    public function index()
    {
        return view('shopify-enhanced::home');
    }

    /**
     * Show the privacy policy page
     */
    public function privacy()
    {
        return view('shopify-enhanced::privacy');
    }
}