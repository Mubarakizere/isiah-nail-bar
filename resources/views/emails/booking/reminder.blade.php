<x-mail::message>
# ⏰ Appointment Reminder

Hi {{ $booking->customer->user->name ?? 'Valued Customer' }},

This is a friendly reminder that your upcoming appointment at **Isaiah Nail Bar** is scheduled as follows:

<x-mail::panel>
**📅 Date:** {{ \Carbon\Carbon::parse($booking->date)->format('F j, Y') }}  
**⏰ Time:** {{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}  
**👩 Provider:** {{ $booking->provider->name ?? '-' }}
</x-mail::panel>

---

### 💅 Services Booked:
@foreach($booking->services as $service)
- **{{ $service->name }}** ({{ $service->duration_minutes }} min)
@endforeach

We’re excited to pamper you!  
If you need to reschedule, please notify us at least 48 hours in advance.

<x-mail::button :url="url('/')">
Visit Our Website
</x-mail::button>

Thanks again,  
**Isaiah Nail Bar Team**  
_@isaiahnailbar • KG 4 Roundabout, Kigali_
</x-mail::message>
