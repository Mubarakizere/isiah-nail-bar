@component('mail::message')
# New Booking Received

A new booking has been made by **{{ $booking->customer->user->name ?? '-' }}**.

### 📋 Booking Details:

- **Services:**
@foreach($booking->services as $service)
  - {{ $service->name }} ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

- **Provider:** {{ $booking->provider->name ?? '-' }}
- **Date & Time:** {{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }} at {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}
- **Payment Option:** {{ ucfirst($booking->payment_option) }}
@if($booking->deposit_amount)
- **Deposit Paid:** RWF {{ number_format($booking->deposit_amount) }}
@endif
- **Reference:** {{ $booking->reference }}
- **Status:** {{ ucfirst($booking->status) }}

@component('mail::button', ['url' => url('/dashboard')])
📂 View Booking in Admin
@endcomponent

Thanks,<br>
**Isaiah Nail Bar**
@endcomponent
