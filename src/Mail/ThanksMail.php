<?php
namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Mail\Mailable;

class ThanksMail extends Mailable
{
    public $shopInfo;

    public function __construct($shopInfo)
    {
        $this->shopInfo = $shopInfo;
    }

    public function build()
    {
        return $this->markdown('emails.thanks')
            ->subject('Thank You for Installing Our App')
            ->with('shop', $this->shopInfo);
    }
}
