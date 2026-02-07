@component('mail::message')
# New Booking Notification

A new booking has been received and requires your attention.

**Customer:** {{ $booking->customer->user->name ?? 'Guest Customer' }}  
**Booking Reference:** {{ $booking->reference }}  
**Status:** {{ ucfirst($booking->status) }}

---

## Booking Details

**Date & Time**  
{{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}

**Assigned Provider**  
{{ $booking->provider->name ?? 'Not assigned' }}

**Selected Services**
@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes) - RWF {{ number_format($service->price, 2) }}
@endforeach

**Total Amount:** RWF {{ number_format($booking->services->sum('price'), 2) }}

---

## Payment Information

**Payment Method:** {{ ucfirst($booking->payment_option) }}
@if($booking->deposit_amount)
**Deposit Paid:** RWF {{ number_format($booking->deposit_amount, 2) }}
@endif

---

@component('mail::button', ['url' => url('/dashboard')])
View Booking Details
@endcomponent

This is an automated notification from your booking management system.

Best regards,  
Isaiah Nail Bar Team
@endcomponent
