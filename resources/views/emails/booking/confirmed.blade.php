@component('mail::message')
# Booking Confirmation

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

Thank you for choosing Isaiah Nail Bar. Your payment has been successfully processed and your appointment is confirmed.

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

**Total Amount:** RWF {{ number_format($booking->services->sum('price'), 2) }}

---

## Payment Information

**Payment Method:** {{ ucfirst($booking->payment_option) }}
@if($booking->deposit_amount)
**Deposit Paid:** RWF {{ number_format($booking->deposit_amount, 2) }}
@endif

---

## Important Information

**Cancellation Policy:** Changes or cancellations must be made at least 48 hours in advance.

**Location:** KG 4 Roundabout, Kigali  
**Contact:** Follow us on Instagram @isaiahnailbar

We look forward to serving you.

Best regards,  
Isaiah Nail Bar Team
@endcomponent
