<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportExpectationMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportReplyMail;

class SupportController extends Controller
{
    /**
     * Display the support page.
     */
    public function index(): Response
    {
        return Inertia::render('Support');
    }

    /**
     * Submit a new support expectation.
     */
    public function submitExpectation(Request $request): JsonResponse
    {
        $request->validate([
            'expectation' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        // Create the support expectation
        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => $request->expectation,
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        // Send email notification to admin
        try {
            Mail::to(config('mail.admin_email', 'support@bestdecoders.com'))
                ->send(new SupportExpectationMail($expectation));
        } catch (\Exception $e) {
            \Log::error('Failed to send support email notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Your support request has been submitted successfully!',
            'expectation_id' => $expectation->id,
        ]);
    }

    /**
     * Get replies for a specific expectation.
     */
    public function getReplies(int $expectationId): JsonResponse
    {
        $expectation = SupportExpectation::where('id', $expectationId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$expectation) {
            return response()->json([
                'success' => false,
                'message' => 'Support request not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'expectation' => $expectation->expectation,
            'replies' => $expectation->replies ?? [],
            'status' => $expectation->status,
            'latest_reply' => $expectation->latest_reply,
            'created_at' => $expectation->created_at,
            'last_reply_at' => $expectation->last_reply_at,
        ]);
    }

    // Admin methods (protected by auth:sanctum middleware)

    /**
     * Get all expectations for admin.
     */
    public function getAllExpectations(Request $request): JsonResponse
    {
        $expectations = SupportExpectation::with('user')
            ->when($request->status, function ($query, $status) {
                return $query->withStatus($status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'expectations' => $expectations,
        ]);
    }

    /**
     * Get a specific expectation for admin.
     */
    public function getExpectation(int $id): JsonResponse
    {
        $expectation = SupportExpectation::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'expectation' => [
                'id' => $expectation->id,
                'user' => [
                    'id' => $expectation->user->id,
                    'name' => $expectation->user->name,
                    'email' => $expectation->user->email,
                    'shop_domain' => $expectation->user->name, // Shop domain is stored in name field
                ],
                'expectation' => $expectation->expectation,
                'replies' => $expectation->replies ?? [],
                'status' => $expectation->status,
                'created_at' => $expectation->created_at,
                'last_reply_at' => $expectation->last_reply_at,
                'app_name' => config('app.name', 'Table of Contents'),
            ],
        ]);
    }

    /**
     * Reply to an expectation (admin only).
     */
    public function replyToExpectation(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'admin_email' => 'sometimes|email',
        ]);

        $expectation = SupportExpectation::findOrFail($id);

        $adminEmail = $request->admin_email ?? 'admin@bestdecoders.com';

        $expectation->addReply($request->message, $adminEmail);

        // Get the latest reply for the email
        $latestReply = $expectation->fresh()->latest_reply;

        // Send email notification to the user
        try {
            Mail::to($expectation->user->email)
                ->send(new SupportReplyMail($expectation, $latestReply));
        } catch (\Exception $e) {
            Log::error('Failed to send support reply email notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully and user has been notified via email!',
            'expectation' => [
                'id' => $expectation->id,
                'status' => $expectation->status,
                'latest_reply' => $expectation->latest_reply,
                'last_reply_at' => $expectation->last_reply_at,
            ],
        ]);
    }
}