<?php 
namespace Bestdecoders\ShopifyLaravelEnhanced\Mail;

use Illuminate\Mail\Mailable;
use App\Models\User;

class AdminGrandfatheredAccessMail extends Mailable
{
    public function __construct(
        public string $shop,
        public string $email,
        public string $message
    ) {}

    public function build()
    {
        return $this->subject("Grandfathered Access Notice - {$this->shop}")
            ->view('shopify-enhanced::emails.admin-grandfathered')
            ->with([
                'shop' => $this->shop,
                'email' => $this->email,
                'messageText' => $this->message,
            ]);
    }
}

