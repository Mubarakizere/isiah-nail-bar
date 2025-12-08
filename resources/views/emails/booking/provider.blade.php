@component('mail::message')
# 📢 New Paid Booking Alert

You have a **new confirmed booking** from:

**👤 Customer:** {{ $booking->customer->user->name ?? 'N/A' }}  
📞 **Phone:** {{ $booking->customer->phone ?? '-' }}  
✉️ **Email:** {{ $booking->customer->user->email ?? '-' }}

---

### 🗓️ Appointment Details

**📅 Date:** {{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}  
**⏰ Time:** {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}  
**🧑 Provider:** {{ $booking->provider->name ?? '-' }}  
@isset($booking->reference)
🔖 **Ref:** {{ $booking->reference }}
@endisset

---

### 💅 Services Booked:
@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} mins) — RWF {{ number_format($service->price) }}
@endforeach

---

@isset($booking->deposit_amount)
💰 **Deposit Paid:** RWF {{ number_format($booking->deposit_amount) }}  
💳 **Remaining Balance:** RWF {{ number_format($booking->services->sum('price') - $booking->deposit_amount) }}
@else
💰 **Total Paid:** RWF {{ number_format($booking->services->sum('price')) }}
@endisset

Please be ready to serve the customer at the scheduled time.

Thanks,  
**Isaiah Nail Bar**
@endcomponent
