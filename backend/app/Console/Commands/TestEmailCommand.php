<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'mail:test {email : The recipient email address}';
    protected $description = 'Send a test email to verify SMTP configuration';

    public function handle(): int
    {
        $recipient = $this->argument('email');
        $mailer = config('mail.default');
        $host = config("mail.mailers.{$mailer}.host");
        $port = config("mail.mailers.{$mailer}.port");
        $from = config('mail.from.address');

        $this->info("Sending test email to: {$recipient}");
        $this->info("Mailer: {$mailer} | Host: {$host}:{$port} | From: {$from}");

        try {
            Mail::raw("Hello!\n\nThis is a test notification from Isaiah Nail Bar sanctuary system.\nIf you received this message, your mail configuration is operational.\n\nSent at: " . now()->toDateTimeString(), function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email — Isaiah Nail Bar Mail Verification');
            });

            $this->info("✅ Test email sent successfully to {$recipient}!");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Mail delivery failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
