@component('mail::message')
# Manual Booking Confirmation

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

Your booking has been manually confirmed by our team. We look forward to serving you.

---

## Appointment Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}  
**Service Provider:** {{ $booking->provider->name ?? 'To be assigned' }}

---

## Services Booked

@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes) - RWF {{ number_format($service->price, 2) }}
@endforeach

@component('mail::button', ['url' => route('booking.receipt', $booking->id)])
View Receipt
@endcomponent

We look forward to seeing you!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
