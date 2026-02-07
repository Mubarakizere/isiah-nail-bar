@component('mail::message')
# Thank You for Your Visit

Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

Thank you for choosing Isaiah Nail Bar for your recent appointment on {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}.

We hope you enjoyed your experience with us.

---

## Share Your Feedback

Your opinion is important to us and helps others make informed decisions. We would greatly appreciate if you could take a moment to share your experience.

@component('mail::button', ['url' => 'https://share.google/OtbpRmUNfC1eA9kmZ'])
Leave a Review on Google
@endcomponent

Alternatively, you may reply to this email with any feedback or suggestions for improvement.

---

Thank you for your continued support.

Best regards,  
Isaiah Nail Bar Team
@endcomponent
