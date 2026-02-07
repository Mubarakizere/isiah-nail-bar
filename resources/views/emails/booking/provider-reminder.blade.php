@component('mail::message')
# Appointment Reminder

Dear {{ $booking->provider->name ?? 'Service Provider' }},

This is a reminder about your upcoming appointment scheduled for tomorrow.

---

## Appointment Details

**Customer:** {{ $booking->customer->user->name ?? 'Client' }}  
**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}

---

## Services to Provide

{{ $booking->services->pluck('name')->implode(', ') }}

---

Please ensure you are prepared and arrive on time to provide excellent service to our customer.

Thank you for your dedication to quality service.

Best regards,  
Isaiah Nail Bar Management
@endcomponent
