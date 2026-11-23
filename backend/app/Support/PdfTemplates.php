<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\Carbon;

/** HTML documents rendered to PDF without a Blade view dependency. */
class PdfTemplates
{
    public static function receipt(Booking $booking, ?string $logo = null): string
    {
        $booking->loadMissing(['customer.user', 'provider', 'services.category', 'pickupLocation', 'payments']);
        $totalServices = (float) $booking->services->sum('price');
        $grandTotal = $totalServices * ($booking->is_home_service ? 2 : 1) + (float) ($booking->pickup_fee ?? 0);
        $serviceRows = $booking->services->map(fn ($service) => '<tr><td>'.self::esc($service->name).'</td><td>'.self::esc($service->category?->name ?? '—').'</td><td>'.(int) ($service->duration_minutes ?? 0).' mins</td><td>RWF '.number_format((float) $service->price).'</td></tr>')->implode('');
        $logoMarkup = $logo ? '<img src="'.self::esc($logo).'" alt="Isaiah Nail Bar Logo" style="height:65px;margin-bottom:10px">' : '';
        $rows = '<tr><td class="label">Receipt ID</td><td>#'.(int) $booking->id.'</td></tr>'
            .'<tr><td class="label">Customer</td><td>'.self::esc($booking->customer?->user?->name ?? '-').'</td></tr>'
            .'<tr><td class="label">Email</td><td>'.self::esc($booking->customer?->user?->email ?? '-').'</td></tr>'
            .'<tr><td class="label">Provider</td><td>'.self::esc($booking->provider?->name ?? '-').'</td></tr>'
            .'<tr><td class="label">Date</td><td>'.self::esc(Carbon::parse($booking->date)->format('D, M j Y')).'</td></tr>'
            .'<tr><td class="label">Time</td><td>'.self::esc(Carbon::parse($booking->time)->format('h:i A')).'</td></tr>';

        if ($booking->is_home_service) {
            $rows .= '<tr><td class="label">Home service</td><td>'.self::esc($booking->address ?? '').'</td></tr>';
        } elseif ($booking->pickup_location_id) {
            $rows .= '<tr><td class="label">Transport pickup</td><td>'.self::esc($booking->pickupLocation?->name ?? 'Route').' ('.self::esc($booking->pickup_address ?? '').')</td></tr>';
        }
        $rows .= '<tr><td class="label">Payment option</td><td>'.self::esc(ucfirst((string) $booking->payment_option)).'</td></tr>';

        $paymentRows = '<tr><td class="label">Base services price</td><td>RWF '.number_format($totalServices).'</td></tr>';
        if ($booking->is_home_service) {
            $paymentRows .= '<tr><td class="label">Home service premium</td><td>+100% (RWF '.number_format($totalServices).')</td></tr>';
        } elseif ($booking->pickup_fee) {
            $paymentRows .= '<tr><td class="label">Pickup transport fee</td><td>RWF '.number_format((float) $booking->pickup_fee).'</td></tr>';
        }
        $paymentRows .= '<tr><td class="label">Grand total</td><td><strong>RWF '.number_format($grandTotal).'</strong></td></tr>';
        if ($booking->deposit_amount) {
            $paymentRows .= '<tr><td class="label">Deposit paid</td><td>RWF '.number_format((float) $booking->deposit_amount).'</td></tr>'
                .'<tr><td class="label">Remaining balance</td><td>RWF '.number_format(max(0, $grandTotal - (float) $booking->deposit_amount)).'</td></tr>';
        } else {
            $paymentRows .= '<tr><td class="label">Paid in full</td><td>Yes</td></tr>';
        }
        if ($booking->reference) {
            $paymentRows .= '<tr><td class="label">Transaction reference</td><td>'.self::esc($booking->reference).'</td></tr>';
        }
        $paymentRows .= '<tr><td class="label">Status</td><td>'.self::esc(ucfirst((string) $booking->status)).'</td></tr>';

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Receipt #'.(int) $booking->id.' - Isaiah Nail Bar</title><style>'
            .'body{font-family:DejaVu Sans,sans-serif;font-size:13px;color:#333;padding:28px 38px}.center{text-align:center}.title{font-size:15px;font-weight:bold;border-bottom:2px solid #6d386a;padding:0 0 6px;margin:20px 0 10px;color:#512c50;text-transform:uppercase}'
            .'table{width:100%;border-collapse:collapse;margin-bottom:18px}.table td,.table th{padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.table th{background:#f9fafb}.label{font-weight:bold;width:38%;background:#f9fafb;color:#374151}.footer{text-align:center;font-size:10px;color:#6b7280;margin-top:36px;line-height:1.6;padding-top:18px;border-top:1px solid #e5e7eb}'
            .'</style></head><body><div class="center">'.$logoMarkup.'<h2>Booking Receipt</h2><p>Isaiah Nail Bar — Kigali</p><small>Generated '.now()->format('d M Y, H:i').'</small></div>'
            .'<div class="title">Booking information</div><table class="table">'.$rows.'</table>'
            .'<div class="title">Services booked</div><table class="table"><thead><tr><th>Service</th><th>Category</th><th>Duration</th><th>Price</th></tr></thead><tbody>'.$serviceRows.'</tbody></table>'
            .'<div class="title">Payment summary</div><table class="table">'.$paymentRows.'</table>'
            .'<div class="footer">Digitally generated receipt, valid without signature.<br>Cancellations should be made at least 48 hours before the appointment.<br>© '.date('Y').' Isaiah Nail Bar · KG 4 Roundabout, Kigali · Tel: 0788 421 063 · Instagram: @isaiahnailbar</div></body></html>';
    }

    public static function revenueReport(iterable $bookings, float|int $total, string $from, string $to): string
    {
        $rows = '';
        foreach ($bookings as $booking) {
            $paid = $booking->deposit_amount && !$booking->is_fully_paid
                ? 'RWF '.number_format((float) $booking->deposit_amount).' (Deposit)'
                : 'RWF '.number_format((float) $booking->services->sum('price'));
            $rows .= '<tr><td>'.(int) $booking->id.'</td><td>'.self::esc($booking->customer?->user?->name ?? '-').'</td><td>'.self::esc($booking->services->pluck('name')->join(', ') ?: 'Deleted Service').'</td><td>'.self::esc($booking->date).'</td><td>'.self::esc(Carbon::parse($booking->time)->format('H:i')).'</td><td>'.self::esc(ucfirst((string) $booking->status)).'</td><td>'.$paid.'</td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="7">No bookings found in this period.</td></tr>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><title>Revenue Report</title><style>body{font:12px DejaVu Sans,sans-serif;color:#333}h2{text-align:center}table{width:100%;border-collapse:collapse;margin-top:18px}th,td{border:1px solid #aaa;padding:6px;text-align:left}th{background:#f4f4f4}.summary{font-weight:bold}.footer{margin-top:36px;text-align:center;font-size:11px;color:#888}</style></head><body>'
            .'<h2>Revenue Report</h2><p class="summary">From: '.self::esc($from).' &nbsp;&nbsp; To: '.self::esc($to).'</p><p class="summary">Total Revenue: RWF '.number_format((float) $total).'</p>'
            .'<table><thead><tr><th>#</th><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Amount Paid</th></tr></thead><tbody>'.$rows.'</tbody></table>'
            .'<div class="footer">Report generated by Isaiah Nail Bar system — '.now()->format('Y-m-d H:i').'</div></body></html>';
    }

    public static function providerEarnings(iterable $bookings, float|int $total, string $providerName, string $today): string
    {
        $rows = '';
        foreach ($bookings as $row) {
            $rows .= '<tr><td>'.self::esc(Carbon::parse($row->date)->format('M d, Y')).'</td><td>'.self::esc(Carbon::parse($row->time)->format('H:i')).'</td><td>'.self::esc($row->name).'</td><td>'.number_format((float) $row->price).'</td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="4">No completed bookings found.</td></tr>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><title>Provider Earnings Report</title><style>body{font:12px DejaVu Sans,sans-serif}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #000;padding:6px;text-align:left}th{background:#eee}.total{text-align:right;margin-top:20px}</style></head><body><h2>Earnings Report</h2><p>Provider: '.self::esc($providerName).'</p><p>Date: '.self::esc($today).'</p><table><thead><tr><th>Date</th><th>Time</th><th>Service</th><th>Amount (RWF)</th></tr></thead><tbody>'.$rows.'</tbody></table><h3 class="total">Total Revenue: RWF '.number_format((float) $total).'</h3></body></html>';
    }

    private static function esc(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
