<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminSettingApiController extends Controller
{
    /**
     * Default fallback public salon info
     */
    public static function defaultSettings(): array
    {
        return [
            'salon_name'           => 'Isaiah Nail Bar',
            'phone'                => '+250 790 395 169',
            'phone_raw'            => '0790395169',
            'whatsapp'             => '+250790395169',
            'email'                => 'info@isaiahnailbar.com',
            'address'              => 'KG 4 Roundabout, Gisementi, Kigali, Rwanda',
            'location_landmark'    => 'Opposite Sonatubes direction, with dedicated client parking',
            'opening_hours'        => 'Monday – Saturday: 08:00 AM – 08:00 PM',
            'opening_hours_sunday' => 'Sunday: Closed (Private bookings upon request)',
            'google_map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15950.019185121285!2d30.108678!3d-1.954736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca6ee46244f67%3A0x6291244078657662!2sKisimenti!5e0!3m2!1sen!2srw!4v1683838383838!5m2!1sen!2srw',
            'google_map_link'      => 'https://maps.google.com/?q=Isaiah+Nail+Bar+Gisementi+Kigali',
            'instagram'            => 'https://www.instagram.com/isaiah_nails_art_kigali_rwanda',
            'facebook'             => 'https://www.facebook.com/isaiahnailbar',
            'google_review_url'    => 'https://g.page/r/CS4QpNuz_MJkEAE/review',
        ];
    }

    public function index(): JsonResponse
    {
        $defaults = self::defaultSettings();

        $dbValues = Setting::query()
            ->whereIn('key', array_merge(array_keys($defaults), ['booking_alert_emails']))
            ->pluck('value', 'key')
            ->all();

        $bookingAlertEmails = $dbValues['booking_alert_emails'] ?? '';

        $merged = array_merge($defaults, array_filter($dbValues, fn ($v) => $v !== null && trim((string) $v) !== ''));

        return response()->json([
            'success' => true,
            'data'    => array_merge($merged, [
                'booking_alert_emails' => $bookingAlertEmails,
                'email_addresses'      => $this->parseAddresses($bookingAlertEmails),
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salon_name'           => ['nullable', 'string', 'max:100'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'phone_raw'            => ['nullable', 'string', 'max:50'],
            'whatsapp'             => ['nullable', 'string', 'max:50'],
            'email'                => ['nullable', 'email', 'max:100'],
            'address'              => ['nullable', 'string', 'max:255'],
            'location_landmark'    => ['nullable', 'string', 'max:255'],
            'opening_hours'        => ['nullable', 'string', 'max:255'],
            'opening_hours_sunday' => ['nullable', 'string', 'max:255'],
            'google_map_embed_url' => ['nullable', 'string', 'max:2000'],
            'google_map_link'      => ['nullable', 'string', 'max:500'],
            'instagram'            => ['nullable', 'string', 'max:255'],
            'facebook'             => ['nullable', 'string', 'max:255'],
            'google_review_url'    => ['nullable', 'string', 'max:500'],
            'booking_alert_emails' => ['nullable', 'string', 'max:5000'],
        ]);

        // Smart Google Maps Embed extraction if user pasted full <iframe ...>
        if (isset($validated['google_map_embed_url'])) {
            $validated['google_map_embed_url'] = $this->sanitizeMapEmbedUrl($validated['google_map_embed_url']);
        }

        // Validate booking alert email format if provided
        if (isset($validated['booking_alert_emails'])) {
            $alertValue = trim((string) $validated['booking_alert_emails']);
            if ($alertValue !== '') {
                $addresses = $this->parseAddresses($alertValue);
                $invalid = array_values(array_filter($addresses, fn (string $email) => !filter_var($email, FILTER_VALIDATE_EMAIL)));

                if ($invalid) {
                    throw ValidationException::withMessages([
                        'booking_alert_emails' => 'Enter valid email addresses separated by commas.',
                    ]);
                }
                $validated['booking_alert_emails'] = implode(', ', array_values(array_unique($addresses)));
            } else {
                $validated['booking_alert_emails'] = '';
            }
        }

        // Auto-clean phone_raw if phone is provided and phone_raw was omitted
        if (!empty($validated['phone']) && empty($validated['phone_raw'])) {
            $validated['phone_raw'] = preg_replace('/[^\d+]/', '', $validated['phone']);
        }

        // Save each provided key into settings
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value !== null ? trim((string) $value) : '']
            );
        }

        return $this->index();
    }

    private function sanitizeMapEmbedUrl(?string $raw): string
    {
        if (!$raw) {
            return '';
        }

        $trimmed = trim($raw);

        // If the user pasted an iframe tag: <iframe ... src="https://..." ...>
        if (preg_match('/<iframe[^>]*\s+src=["\']([^"\']+)["\'][^>]*>/i', $trimmed, $matches)) {
            return trim($matches[1]);
        }

        // Strip any wrapping quotes if pasted like "https://..."
        return trim($trimmed, '"\'');
    }

    private function parseAddresses(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
