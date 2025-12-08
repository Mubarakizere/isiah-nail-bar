<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review</title>
</head>
<body style="font-family: sans-serif; color: #333;">
    <h2>Hello {{ $booking->customer->user->name ?? 'Valued Customer' }},</h2>
    <p>Thank you for trusting us with your recent appointment on {{ \Carbon\Carbon::parse($booking->date)->format('F j, Y') }}.</p>
    <p>We’d love to hear your thoughts! Your feedback helps us improve and serve you better.</p>

    <p>
        <a href="{{ url('/dashboard/reviews/create?booking_id=' . $booking->id) }}"
           style="padding: 10px 20px; background-color: #111; color: white; text-decoration: none; border-radius: 4px;">
           Leave a Review
        </a>
    </p>

    <p style="margin-top: 30px;">Thank you,<br>Isaiah Nail Bar Team</p>
</body>
</html>
