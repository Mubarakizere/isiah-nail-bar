<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Provider;
use Carbon\Carbon;

/**
 * Small, self-contained HTML email templates for the headless Laravel backend.
 * These intentionally avoid Laravel view files so the backend can run without Blade templates.
 */
class EmailTemplates
{
    public static function booking(Booking $booking, string $variant): string
    {
        $booking->loadMissing(['customer.user', 'provider', 'services.category', 'pickupLocation']);

        $name = self::esc($booking->customer?->user?->name ?? 'Valued Customer');
        $provider = self::esc($booking->provider?->name ?? 'To be assigned');
        $date = self::esc(Carbon::parse($booking->date)->format('l, F j, Y'));
        $time = self::esc(Carbon::parse($booking->time)->format('g:i A'));
        $services = $booking->services;
        $baseTotal = (float) $services->sum('price');
        $total = $baseTotal * ($booking->is_home_service ? 2 : 1) + (float) ($booking->pickup_fee ?? 0);
        $serviceRows = $services->map(fn ($service) => '<tr><td>'.self::esc($service->name).'</td><td>'.(int) ($service->duration_minutes ?? 0).' min</td><td>RWF '.number_format((float) $service->price, 0).'</td></tr>')->implode('');

        $titles = [
            'confirmed' => 'Booking Confirmation',
            'created' => 'Booking Received',
            'paid' => 'Payment Confirmation',
            'admin' => 'New Booking Notification',
            'reminder' => 'Appointment Reminder',
            'provider' => 'New Booking Assignment',
            'provider-reminder' => 'Appointment Reminder',
            'manual' => 'Manual Booking Confirmation',
        ];
        $title = $titles[$variant] ?? 'Booking Update';
        $greeting = in_array($variant, ['admin', 'provider', 'provider-reminder'], true)
            ? 'Hello '.($variant === 'admin' ? 'Isaiah Nail Bar team' : $provider).','
            : 'Dear '.$name.',';
        $intro = match ($variant) {
            'created' => 'Your appointment request has been received. Please complete payment to confirm your booking.',
            'paid' => 'Thank you for your payment. Your booking is confirmed and we look forward to serving you.',
            'admin' => 'A new booking has been received and requires your attention.',
            'provider' => 'You have been assigned a new confirmed booking.',
            'provider-reminder' => 'This is a reminder about your upcoming appointment.',
            'reminder' => 'This is a reminder about your upcoming appointment with Isaiah Nail Bar.',
            'manual' => 'Your booking has been manually confirmed by our team. We look forward to serving you.',
            default => 'Thank you for choosing Isaiah Nail Bar. Your appointment is confirmed.',
        };

        $rows = '<tr><th>Booking</th><td>#'.(int) $booking->id.'</td></tr>'
            .'<tr><th>Date</th><td>'.$date.'</td></tr>'
            .'<tr><th>Time</th><td>'.$time.'</td></tr>'
            .'<tr><th>Service provider</th><td>'.$provider.'</td></tr>'
            .'<tr><th>Status</th><td>'.self::esc(ucfirst((string) $booking->status)).'</td></tr>';

        if ($booking->is_home_service) {
            $rows .= '<tr><th>Home service</th><td>'.self::esc($booking->address ?? '').'</td></tr>';
        } elseif ($booking->pickup_location_id) {
            $rows .= '<tr><th>Pickup</th><td>'.self::esc($booking->pickupLocation?->name ?? 'Route').' — '.self::esc($booking->pickup_address ?? '').'</td></tr>';
        }

        if ($booking->payment_option) {
            $rows .= '<tr><th>Payment method</th><td>'.self::esc(ucfirst((string) $booking->payment_option)).'</td></tr>';
        }
        if ($booking->reference) {
            $rows .= '<tr><th>Reference</th><td>'.self::esc($booking->reference).'</td></tr>';
        }

        $paymentRows = '<tr><th>Total</th><td>RWF '.number_format($total, 0).'</td></tr>';
        if ($booking->deposit_amount) {
            $paymentRows .= '<tr><th>Deposit paid</th><td>RWF '.number_format((float) $booking->deposit_amount, 0).'</td></tr>';
            $paymentRows .= '<tr><th>Balance remaining</th><td>RWF '.number_format(max(0, $total - (float) $booking->deposit_amount), 0).'</td></tr>';
        }

        $cta = '';
        if (!in_array($variant, ['reminder', 'provider-reminder'], true)) {
            $baseUrl = rtrim((string) config('app.url'), '/');
            $target = in_array($variant, ['admin', 'provider'], true)
                ? $baseUrl.'/admin/bookings'
                : $baseUrl.'/dashboard';
            $cta = '<p style="margin:24px 0"><a href="'.self::esc($target).'" style="display:inline-block;background:#6d386a;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none">'.($variant === 'created' ? 'Complete payment' : 'View booking').'</a></p>';
        }

        $salonRecipient = in_array($variant, ['admin', 'provider', 'provider-reminder'], true);
        $recipientRows = '';
        if ($salonRecipient) {
            $recipientRows = '<tr><th>Customer</th><td>'.$name.'</td></tr>';
            if ($booking->customer?->phone) {
                $recipientRows .= '<tr><th>Phone</th><td>'.self::esc($booking->customer->phone).'</td></tr>';
            }
            if ($booking->customer?->user?->email) {
                $recipientRows .= '<tr><th>Email</th><td>'.self::esc($booking->customer->user->email).'</td></tr>';
            }
        }

        return self::layout($title, '<p>'.$greeting.'</p><p>'.self::esc($intro).'</p>'
            .'<h2>Appointment details</h2><table><tbody>'.$recipientRows.$rows.'</tbody></table>'
            .'<h2>Services</h2><table><thead><tr><th>Service</th><th>Duration</th><th>Price</th></tr></thead><tbody>'.$serviceRows.'</tbody></table>'
            .'<h2>Payment summary</h2><table><tbody>'.$paymentRows.'</tbody></table>'.$cta
            .'<p>KG 4 Roundabout, Kigali · Instagram @isaiahnailbar</p>'
            .'<p>Best regards,<br>Isaiah Nail Bar Team</p>');
    }

    public static function providerCredentials(Provider $provider, string $password, string $loginUrl): string
    {
        return self::layout('Welcome to Isaiah Nail Bar',
            '<p>Dear '.self::esc($provider->name).',</p>'
            .'<p>Your service provider account has been created.</p>'
            .'<h2>Login credentials</h2><table><tbody>'
            .'<tr><th>Email</th><td>'.self::esc($provider->email).'</td></tr>'
            .'<tr><th>Temporary password</th><td>'.self::esc($password).'</td></tr>'
            .'</tbody></table><p>Please sign in and change your password as soon as possible.</p>'
            .'<p><a href="'.self::esc($loginUrl).'" style="display:inline-block;background:#6d386a;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none">Log in</a></p>'
            .'<p>Best regards,<br>Isaiah Nail Bar Management</p>');
    }

    public static function reviewRequest(Booking $booking): string
    {
        $booking->loadMissing(['customer.user', 'services']);
        $services = self::esc($booking->services->pluck('name')->implode(', '));
        $date = self::esc(Carbon::parse($booking->date)->format('l, F j, Y'));

        return self::layout('Thank you for your visit',
            '<p>Dear '.self::esc($booking->customer?->user?->name ?? 'Valued Customer').',</p>'
            .'<p>Thank you for choosing Isaiah Nail Bar for your visit on '.$date.'. We hope you enjoyed the experience.</p>'
            .'<p><strong>Services:</strong> '.$services.'</p>'
            .'<p>Your feedback helps other clients discover us. We would be grateful if you shared your experience.</p>'
            .'<p><a href="https://g.page/r/CS4QpNuz_MJkEAE/review" style="display:inline-block;background:#6d386a;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none">Review us on Google</a></p>'
            .'<p>Best regards,<br>Isaiah Nail Bar Team</p>');
    }

    public static function rebookingReminder(Booking $booking): string
    {
        $booking->loadMissing(['customer.user', 'services', 'provider']);
        $name = self::esc($booking->customer?->user?->name ?? $booking->customer?->name ?? 'Valued Customer');
        $services = self::esc($booking->services->pluck('name')->implode(', '));
        $provider = self::esc($booking->provider?->name ?? 'our salon team');
        $date = self::esc(Carbon::parse($booking->date)->format('l, F j, Y'));
        $bookingUrl = rtrim((string) config('app.url'), '/').'/booking/step1';

        return self::layout('Time for Your 3-Week Nail Refill & Care',
            '<p>Dear '.$name.',</p>'
            .'<p>It has been three weeks since your appointment with '.$provider.' on '.$date.'.</p>'
            .'<p>To preserve the health and flawless elegance of your nails, high-end manicures and extensions typically thrive with a maintenance refill or refresh every 2 to 3 weeks.</p>'
            .($services !== '' ? '<div style="background:#faf8fa;border-left:4px solid #6d386a;padding:12px 16px;margin:20px 0"><p style="margin:0;font-size:14px;color:#706970">Your last services:</p><p style="margin:4px 0 0;font-weight:bold;color:#302b30">'.$services.'</p></div>' : '')
            .'<p>We would love to welcome you back to the sanctuary for your refill, treatment, or fresh design.</p>'
            .'<p style="margin:28px 0"><a href="'.self::esc($bookingUrl).'" style="display:inline-block;background:#6d386a;color:#fff;padding:14px 24px;border-radius:6px;text-decoration:none;font-weight:bold;letter-spacing:0.5px">Book Your Next Appointment</a></p>'
            .'<p>Need custom timing or consultation? Feel free to reply to this email or call us directly.</p>'
            .'<p>KG 4 Roundabout, Kigali · Instagram @isaiahnailbar</p>'
            .'<p>Warm regards,<br>Isaiah Nail Bar Team</p>');
    }

    private static function layout(string $title, string $body): string
    {
        return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.self::esc($title).'</title></head>'
            .'<body style="margin:0;background:#f5f2f5;font-family:Arial,sans-serif;color:#302b30"><div style="max-width:640px;margin:24px auto;background:#fff;border-radius:10px;overflow:hidden">'
            .'<header style="background:#6d386a;color:#fff;padding:22px 28px"><strong>Isaiah Nail Bar</strong></header>'
            .'<main style="padding:28px">'.$body.'</main><footer style="padding:18px 28px;background:#faf8fa;color:#706970;font-size:12px">KG 4 Roundabout, Kigali · © '.date('Y').' Isaiah Nail Bar</footer>'
            .'</div></body></html>';
    }

    private static function esc(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
