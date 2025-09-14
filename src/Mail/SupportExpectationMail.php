<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;

class SupportExpectationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SupportExpectation $expectation
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shopDomain = $this->expectation->user->name ?? 'Unknown Shop';
        $appName = config('app.name', 'Table of Contents');

        return new Envelope(
            subject: "New Support Request from {$shopDomain} - {$appName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support-expectation',
            with: [
                'expectation' => $this->expectation,
                'user' => $this->expectation->user,
                'shopDomain' => $this->expectation->user->name ?? 'Unknown Shop',
                'appName' => config('app.name', 'Table of Contents'),
                'expectationMessage' => $this->expectation->expectation['message'] ?? 'No message provided',
                'userEmail' => $this->expectation->user->email,
                'submittedAt' => $this->expectation->created_at,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}