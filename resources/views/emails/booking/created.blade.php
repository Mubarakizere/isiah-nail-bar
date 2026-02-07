@component('mail::message')
# Booking Received

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

Thank you for choosing Isaiah Nail Bar. Your appointment request has been received and your time slot has been reserved.

**Payment Required:** Please complete your payment to confirm your booking.

---

## Appointment Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}  
**Service Provider:** {{ $booking->provider->name ?? 'To be assigned' }}

---

## Services Selected

@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes) - RWF {{ number_format($service->price, 2) }}
@endforeach

**Total Amount Due:** RWF {{ number_format($booking->services->sum('price'), 2) }}

---

## Next Steps

Your appointment slot is currently reserved. To confirm your booking, please complete the payment process.

**Status:** Awaiting Payment

@component('mail::button', ['url' => url('/')])
Complete Payment
@endcomponent

---

**Need Assistance?**  
Contact us on Instagram @isaiahnailbar or visit us at KG 4 Roundabout, Kigali.

Best regards,  
Isaiah Nail Bar Team
@endcomponent
