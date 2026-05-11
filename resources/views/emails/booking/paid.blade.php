@component('mail::message')
# Payment Confirmation

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

Thank you for your payment. Your booking has been confirmed and we look forward to serving you.

---

## Appointment Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}  
**Service Provider:** {{ $booking->provider->name ?? 'To be assigned' }}  
@if($booking->is_home_service)
**Home Service:** {{ $booking->address }}
@elseif($booking->pickup_location_id)
**Transport Pickup:** {{ $booking->pickupLocation->name ?? 'Route' }} ({{ $booking->pickup_address }})
@endif
**Booking Reference:** {{ $booking->reference }}

---

## Services Booked

@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes) - RWF {{ number_format($service->price, 2) }}
@endforeach

---

## Payment Summary

@php
    $emailTotal = $booking->services->sum('price');
    if ($booking->is_home_service) $emailTotal *= 2;
    if ($booking->pickup_fee) $emailTotal += $booking->pickup_fee;
@endphp
@if($booking->deposit_amount)
**Deposit Paid:** RWF {{ number_format($booking->deposit_amount, 2) }}  
**Remaining Balance:** RWF {{ number_format($emailTotal - $booking->deposit_amount, 2) }}
@else
**Total Amount Paid:** RWF {{ number_format($emailTotal, 2) }}
@endif

---

Your receipt is attached to this email. You can also view it online using the button below.

@component('mail::button', ['url' => route('booking.receipt', $booking->id)])
View Receipt Online
@endcomponent

---

**Location:** KG 4 Roundabout, Kigali  
**Follow us:** Instagram @isaiahnailbar

We appreciate your business.

---

💬 **Love your nails?** We'd be so grateful if you could leave us a quick Google review. It takes just 30 seconds!

@component('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review'])
⭐ Review Us on Google
@endcomponent

Best regards,  
Isaiah Nail Bar Team
@endcomponent
