@component('mail::message')
# Hello {{ $booking->customer->user->name ?? 'Valued Customer' }},

📥 We’ve received your booking request for the following services:

@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

---

### 🗓 Booking Details:
- **Date:** {{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}  
- **Time:** {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}  
- **Provider:** {{ $booking->provider->name ?? '-' }}  
- **Status:** Pending Confirmation

You’ll receive another email once the provider confirms your appointment.

@component('mail::button', ['url' => url('/')])
Visit Website
@endcomponent

Thanks for booking with us!  
**Isaiah Nail Bar**

_📍 KG 4 Roundabout, Kigali • 📱 IG: @isaiahnailbar_
@endcomponent
