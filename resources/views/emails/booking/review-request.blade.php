<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review</title>
</head>
<body>
    @component('mail::message')
    # Thank You for Visiting Us

    Dear {{ $booking->customer->user->name ?? 'Valued Customer' }},

    Thank You for choosing Isaiah Nail Bar. We hope you enjoyed your experience with us.

    ---

    ## Your Recent Visit

    **Date:** {{ \Carbon\Carbon::parse($booking->date)->format('l, F j, Y') }}
    **Services:** {{ $booking->services->pluck('name')->implode(', ') }}

    ---

    ## ⭐ Leave Us a Google Review

    Your feedback helps us grow and helps other clients find us. We'd be so grateful if you could take just **30 seconds** to share your experience on Google.

    @component('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review', 'color' => 'primary'])
    ⭐ Review Us on Google
    @endcomponent

    Even a simple star rating makes a big difference! ❤️

    ---

    Thank you for your continued support.

    Best regards,
    Isaiah Nail Bar Team
    @endcomponent
</body>
</html>

