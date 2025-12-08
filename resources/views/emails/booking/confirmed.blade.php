@component('mail::message')
# Hi {{ $booking->customer->user->name ?? 'Valued Customer' }},

✅ Your payment has been received for the following services:

@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

### 📅 Your appointment is confirmed:
- **Date:** {{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}  
- **Time:** {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}  
- **Provider:** {{ $booking->provider->name ?? '-' }}  
- **Payment Option:** {{ ucfirst($booking->payment_option) }}

@if($booking->deposit_amount)
- **Deposit Paid:** RWF {{ number_format($booking->deposit_amount) }}
@endif

---

If you need to **reschedule or cancel**, please contact us **at least 48 hours in advance**.

Thank you for choosing us!  
**Isaiah Nail Bar**

_📍 KG 4 Roundabout, Kigali • 📱 IG: @isaiahnailbar_
@endcomponent
