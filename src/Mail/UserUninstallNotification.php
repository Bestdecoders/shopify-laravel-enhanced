<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserUninstallNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $shopDomain;

    public function __construct($shopDomain)
    {
        $this->shopDomain = $shopDomain;
    }

    public function build()
    {
        return $this->markdown('emails.user-uninstall')
            ->subject("You Uninstalled the App: {$this->shopDomain}")
            ->with(['shopDomain' => $this->shopDomain]);
    }
}
