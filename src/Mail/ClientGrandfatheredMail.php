<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Mail\Mailable;

class ClientGrandfatheredMail extends Mailable
{
    public function __construct(public $user, public $message) {}

    public function build()
    {
        return $this->subject('Shopify App Free Access Notice')
                    ->view('shopify-enhanced::emails.grandfathered')
                    ->with([
                        'user' => $this->user,
                        'messageText' => $this->message,
                    ]);
    }
}
