@component('mail::message')
# Booking Confirmed!

Hello {{ $customer->name }},

Your booking at **Isaiah Nail Bar** has been confirmed!

## Booking Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ $booking->time }}  
**Provider:** {{ $booking->provider->name }}

## Services

@foreach($booking->services as $service)
- {{ $service->name }} - RWF {{ number_format($service->price) }}
@endforeach

---

**Total:** RWF {{ number_format($booking->services->sum('price')) }}

@if($booking->payment_option === 'deposit')
**Deposit Paid:** RWF {{ number_format($booking->deposit_amount) }}  
**Remaining:** RWF {{ number_format($booking->services->sum('price') - $booking->deposit_amount) }}
@endif

@component('mail::button', ['url' => route('booking.receipt', $booking->id)])
View Receipt
@endcomponent

We look forward to seeing you!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
