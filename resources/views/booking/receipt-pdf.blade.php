<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $booking->id }} - Isaiah Nail Bar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #333;
            padding: 30px 40px;
        }
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 24px; }
        .logo { height: 65px; margin-bottom: 10px; }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 6px;
            margin-bottom: 12px;
            margin-top: 20px;
            text-transform: uppercase;
            color: #1e40af;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .table td.label {
            font-weight: bold;
            width: 40%;
            background-color: #f9fafb;
            color: #374151;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-top: 40px;
            line-height: 1.6;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled, .status-declined { background: #fee2e2; color: #991b1b; }
        .status-accepted { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="text-center mb-4">
        @if($logo)
            <img src="{{ $logo }}" alt="Isaiah Nail Bar Logo" class="logo">
        @endif
        <h2 class="mb-2">Booking Receipt</h2>
        <p class="mb-2" style="color: #6b7280;">Isaiah Nail Bar — Kigali</p>
        <small style="color: #9ca3af;">Generated on {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</small>
    </div>

    {{-- Booking Details --}}
    <div class="section-title">Booking Information</div>
    <table class="table">
        <tr><td class="label">Receipt ID:</td><td>#{{ $booking->id }}</td></tr>
        <tr><td class="label">Customer:</td><td>{{ $booking->customer->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Email:</td><td>{{ $booking->customer->user->email ?? '-' }}</td></tr>
        <tr><td class="label">Provider:</td><td>{{ $booking->provider->name ?? '-' }}</td></tr>
        <tr><td class="label">Date:</td><td>{{ \Carbon\Carbon::parse($booking->date)->format('D, M j Y') }}</td></tr>
        <tr><td class="label">Time:</td><td>{{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</td></tr>
        <tr><td class="label">Payment Option:</td><td>{{ ucfirst($booking->payment_option) }}</td></tr>
    </table>

    {{-- Services --}}
    <div class="section-title">Services Booked</div>
    <table class="table">
        <tr>
            <td class="label">Service Details:</td>
            <td>
                <ul style="margin: 0; padding-left: 16px;">
                   @foreach($booking->services as $service)
                        <li>
                            {{ $service->name }} — {{ $service->category->name ?? '—' }} —
                            {{ $service->duration_minutes }} mins —
                            RWF {{ number_format($service->price) }}
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
    </table>

    {{-- Payment Summary --}}
    <div class="section-title">Payment Summary</div>
    <table class="table">
        <tr>
            <td class="label">Total Services Price:</td>
            <td style="font-weight: bold;">RWF {{ number_format($booking->services->sum('price')) }}</td>
        </tr>
        @if($booking->deposit_amount)
            <tr><td class="label">Deposit Paid:</td><td>RWF {{ number_format($booking->deposit_amount) }}</td></tr>
            <tr><td class="label">Remaining Balance:</td><td>RWF {{ number_format($booking->services->sum('price') - $booking->deposit_amount) }}</td></tr>
        @else
            <tr><td class="label">Paid in Full:</td><td>Yes</td></tr>
        @endif

        @if(!empty($booking->reference))
            <tr><td class="label">Transaction Ref:</td><td>{{ $booking->reference }}</td></tr>
        @endif

        <tr>
            <td class="label">Status:</td>
            <td>
                <span class="status status-{{ $booking->status }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        This receipt is digitally generated and valid without signature.<br>
        Cancellations must be made at least 48 hours before the scheduled time.<br><br>
        &copy; {{ date('Y') }} Isaiah Nail Bar — All Rights Reserved.<br>
        KG 4 Roundabout, Kigali — Tel: 0788 421 063 — IG: @isaiahnailbar
    </div>
</body>
</html>
