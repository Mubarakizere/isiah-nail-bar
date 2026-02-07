@component('mail::message')
# New Booking Assignment

You have been assigned a new confirmed booking.

---

## Customer Information

**Name:** {{ $booking->customer->user->name ?? 'N/A' }}  
**Phone:** {{ $booking->customer->phone ?? 'Not provided' }}  
**Email:** {{ $booking->customer->user->email ?? 'Not provided' }}

---

## Appointment Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}  
**Assigned Provider:** {{ $booking->provider->name ?? 'Not assigned' }}  
**Booking Reference:** {{ $booking->reference }}

---

## Services Scheduled

@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes) - RWF {{ number_format($service->price, 2) }}
@endforeach

**Total Value:** RWF {{ number_format($booking->services->sum('price'), 2) }}

---

## Payment Status

@isset($booking->deposit_amount)
**Deposit Received:** RWF {{ number_format($booking->deposit_amount, 2) }}  
**Balance Due:** RWF {{ number_format($booking->services->sum('price') - $booking->deposit_amount, 2) }}
@else
**Total Amount Paid:** RWF {{ number_format($booking->services->sum('price'), 2) }}
@endisset

---

Please ensure you are available and prepared to serve this customer at the scheduled time.

Best regards,  
Isaiah Nail Bar Management
@endcomponent
