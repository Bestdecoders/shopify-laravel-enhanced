<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminUninstallNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $shopDomain;

    public function __construct($shopDomain)
    {
        $this->shopDomain = $shopDomain;
    }

    public function build()
    {
        return $this->markdown('emails.admin-uninstall')
            ->subject("App Uninstalled: {$this->shopDomain}")
            ->with(['shopDomain' => $this->shopDomain]);
    }
}
