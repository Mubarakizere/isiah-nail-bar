@component('mail::message')
# Appointment Reminder

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

This is a reminder that you have an appointment scheduled with Isaiah Nail Bar tomorrow.

---

## Appointment Details

**Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}  
**Time:** {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}  
**Service Provider:** {{ $booking->provider->name ?? 'To be assigned' }}

---

## Scheduled Services

@foreach($booking->services as $service)
- {{ $service->name }} ({{ $service->duration_minutes }} minutes)
@endforeach

---

## Important Information

**Cancellation Policy:** If you need to reschedule or cancel your appointment, please contact us at least 24 hours in advance.

**Location:** KG 4 Roundabout, Kigali  
**Contact:** Instagram @isaiahnailbar

We look forward to seeing you.

Best regards,  
Isaiah Nail Bar Team
@endcomponent
