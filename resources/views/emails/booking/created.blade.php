@component('mail::message')
# Hello {{ $booking->customer->user->name ?? 'Valued Customer' }},

## 🎉 Almost There! Your Appointment is Reserved

We've reserved your time slot, but **payment is required** to confirm your booking.

### Selected Services:
@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

---

### 📋 Booking Details:
- **Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
- **Time:** {{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}  
- **Provider:** {{ $booking->provider->name ?? '-' }}  
- **Amount Due:** RWF {{ number_format($booking->services->sum('price')) }}
- **Status:** ⏳ Awaiting Payment

---

### ⚠️ Important Notice
Your booking will be **confirmed once payment is received**. Please complete your payment to secure your appointment slot.

@component('mail::button', ['url' => url('/')])
Complete Payment Now
@endcomponent

Need help? Feel free to contact us!

Thanks for choosing us,  
**Isaiah Nail Bar**

_📍 KG 4 Roundabout, Kigali • 📱 IG: @isaiahnailbar_
@endcomponent
