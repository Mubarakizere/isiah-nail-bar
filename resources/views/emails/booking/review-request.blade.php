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

    ## Share Your Feedback

    Your opinion matters to us. We would greatly appreciate it if you could take a moment to share your experience.

    @component('mail::button', ['url' => $reviewUrl ?? url('/')])
    Leave a Review
    @endcomponent

    ---

    Thank you for your continued support.

    Best regards,
    Isaiah Nail Bar Team
    @endcomponent
</body>
</html>
