<?php

namespace App\Mail;

use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $password;
    public $loginUrl;

    public function __construct(Provider $provider, string $password)
    {
        $this->provider = $provider;
        $this->password = $password;
        $this->loginUrl = url('/login');
    }

    public function build()
    {
        return $this->subject('Your Isaiah Nail Bar Provider Account Credentials')
                    ->view('emails.provider-credentials');
    }
}
