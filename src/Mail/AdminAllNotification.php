<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAllNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $shopDomain, $subject,$data;

    public function __construct($shopDomain, $data = [], $subject)
    {
        $this->shopDomain = $shopDomain;
        $this->subject = $subject;
        $this->data = $data;
    }

    public function build()
    {
        return $this->markdown('emails.admin-all-notification')
            ->subject($this->subject)
            ->with(['shopDomain' => $this->shopDomain, 'data' => $this->data]);
    }
}
