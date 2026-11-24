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
            $htmlBody = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Sanctuary System Verification</title></head>'
                . '<body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background-color: #faf7f5; margin: 0; padding: 24px; color: #221c1a;">'
                . '<div style="max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e8e2dc; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">'
                . '<div style="background-color: #221c1a; padding: 26px 24px; text-align: center;">'
                . '<h1 style="color: #b8975a; margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Isaiah Nail Bar</h1>'
                . '<p style="color: #ffffff; margin: 6px 0 0; font-size: 12px; letter-spacing: 1px;">Kigali Premier Boutique Sanctuary</p>'
                . '</div>'
                . '<div style="padding: 32px 24px;">'
                . '<h2 style="font-size: 18px; color: #221c1a; margin-top: 0; font-weight: 600;">Sanctuary Messaging System Operational</h2>'
                . '<p style="font-size: 14px; line-height: 1.6; color: #5c5550;">This notification confirms that your official domain email channel (<strong>' . htmlspecialchars($from) . '</strong>) is active, securely authenticated, and delivering appointments and receipts.</p>'
                . '<div style="background: #faf7f5; border-left: 4px solid #b8975a; padding: 14px 18px; margin: 22px 0; border-radius: 4px;">'
                . '<p style="margin: 0; font-size: 13px; color: #221c1a;"><strong>Domain:</strong> isaiahnailbar.com</p>'
                . '<p style="margin: 4px 0 0; font-size: 13px; color: #221c1a;"><strong>Sender Host:</strong> ' . htmlspecialchars($host) . ':' . $port . '</p>'
                . '<p style="margin: 4px 0 0; font-size: 13px; color: #221c1a;"><strong>Security:</strong> Authenticated SSL/TLS</p>'
                . '<p style="margin: 4px 0 0; font-size: 13px; color: #221c1a;"><strong>Dispatched At:</strong> ' . now()->toRfc2822String() . '</p>'
                . '</div>'
                . '<p style="font-size: 13px; line-height: 1.6; color: #5c5550;">Clients reserving treatments will receive automated booking vouchers, 15-minute appointment reminders, and service follow-ups through this verified sender.</p>'
                . '</div>'
                . '<div style="background: #faf7f5; border-top: 1px solid #e8e2dc; padding: 18px 24px; text-align: center; font-size: 11px; color: #8e857f;">'
                . '<p style="margin: 0; font-weight: 500;">Isaiah Nail Bar · KG 4 Roundabout, Kigali, Rwanda</p>'
                . '<p style="margin: 4px 0 0;">Concierge: +250 790 395 169 · <a href="https://isaiahnailbar.com" style="color: #b8975a; text-decoration: none;">isaiahnailbar.com</a></p>'
                . '</div>'
                . '</div>'
                . '</body></html>';

            $textBody = "Isaiah Nail Bar — Kigali Premier Boutique Sanctuary\n\n"
                . "Sanctuary Messaging System Operational\n\n"
                . "This notification confirms that your official domain email channel (" . $from . ") is active, securely authenticated, and delivering appointments and receipts.\n\n"
                . "Domain: isaiahnailbar.com\n"
                . "Sender Host: " . $host . ":" . $port . "\n"
                . "Security: Authenticated SSL/TLS\n"
                . "Dispatched At: " . now()->toRfc2822String() . "\n\n"
                . "Isaiah Nail Bar · KG 4 Roundabout, Kigali, Rwanda\n"
                . "Concierge: +250 790 395 169 · https://isaiahnailbar.com";

            Mail::html($htmlBody, function ($message) use ($recipient, $textBody) {
                $message->to($recipient)
                    ->subject('Appointment Notification Service — Isaiah Nail Bar')
                    ->text($textBody);
            });

            $this->info("✅ Test email sent successfully to {$recipient}!");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Mail delivery failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
