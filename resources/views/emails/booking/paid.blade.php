@component('mail::message')
# 🎉 Your Payment is Confirmed!

Hi {{ $booking->customer->user->name ?? 'there' }},

Thank you for booking with **Isaiah Nail Bar**.  
✅ Your payment has been successfully received, and your appointment is confirmed!

---

### 💅 Services Booked:
@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

---

@component('mail::panel')
📅 **Date**: {{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}  
⏰ **Time**: {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}  
👩‍🔧 **Provider**: {{ $booking->provider->name ?? '—' }}  
@isset($booking->reference)
🔖 **Ref**: {{ $booking->reference }}
@endisset
@endcomponent

@if($booking->deposit_amount)
💰 **Amount Paid (Deposit):** RWF {{ number_format($booking->deposit_amount) }}  
💳 **Remaining Balance:** RWF {{ number_format($booking->services->sum('price') - $booking->deposit_amount) }}
@else
💰 **Total Paid:** RWF {{ number_format($booking->services->sum('price')) }}
@endif

Your receipt is attached, and you can also view it online below.

@component('mail::button', ['url' => route('booking.receipt', $booking->id)])
📄 View Online Receipt
@endcomponent

Thanks again,  
**Isaiah Nail Bar** 💅

_📍 KG 4 Roundabout, Kigali • IG: [@isaiahnailbar](https://instagram.com/isaiahnailbar)_
@endcomponent
