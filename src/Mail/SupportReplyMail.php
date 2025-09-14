<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;

class SupportReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SupportExpectation $expectation,
        public array $latestReply
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Table of Contents');

        return new Envelope(
            subject: "Response to your support request - {$appName}",
            replyTo: [
                config('mail.reply_to.address', 'support@bestdecoders.com') => config('mail.reply_to.name', 'Support Team')
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support-reply',
            with: [
                'expectation' => $this->expectation,
                'user' => $this->expectation->user,
                'shopDomain' => $this->expectation->user->name ?? 'Your Shop',
                'appName' => config('app.name', 'Table of Contents'),
                'originalMessage' => $this->expectation->expectation['message'] ?? 'No message provided',
                'latestReply' => $this->latestReply,
                'adminEmail' => $this->latestReply['admin_email'] ?? 'Support Team',
                'replyMessage' => $this->latestReply['message'] ?? 'No reply message',
                'repliedAt' => $this->latestReply['created_at'] ?? now(),
                'supportEmail' => config('mail.reply_to.address', 'support@bestdecoders.com'),
                'expectationId' => $this->expectation->id,
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