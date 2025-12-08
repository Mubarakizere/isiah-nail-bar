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
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .logo { height: 65px; margin-bottom: 10px; }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #444;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        .table td.label {
            font-weight: bold;
            width: 40%;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 40px;
            line-height: 1.6;
        }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled,
        .status-declined { background: #f8d7da; color: #721c24; }
        .status-accepted { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="text-center mb-4">
        @if($logo)
            <img src="{{ $logo }}" alt="Isaiah Nail Bar Logo" class="logo">
        @endif
        <h2 class="mb-2">Booking Receipt</h2>
        <p class="mb-2">Isaiah Nail Bar — Kigali</p>
        <small class="text-muted">Generated on {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</small>
    </div>

    {{-- Booking Details --}}
    <div class="section-title">Booking Information</div>
    <table class="table">
        <tr><td class="label">Receipt ID:</td><td>#{{ $booking->id }}</td></tr>
        <tr><td class="label">Customer:</td><td>{{ $booking->customer->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Email:</td><td>{{ $booking->customer->user->email ?? '-' }}</td></tr>
        <tr><td class="label">Provider:</td><td>{{ $booking->provider->name ?? '-' }}</td></tr>
        <tr><td class="label">Date:</td><td>{{ \Carbon\Carbon::parse($booking->date)->format('D, M j Y') }}</td></tr>
        <tr><td class="label">Time:</td><td>{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</td></tr>
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
            <td>RWF {{ number_format($booking->services->sum('price')) }}</td>
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
