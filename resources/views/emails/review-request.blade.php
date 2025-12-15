<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review</title>
</head>
<body style="font-family: sans-serif; color: #333;">
    <h2>Hello {{ $booking->customer->user->name ?? 'Valued Customer' }},</h2>
    <p>Thank you for trusting us with your recent appointment on {{ \Carbon\Carbon::parse($booking->date)->format('F j, Y') }}.</p>
    <p>We hope you enjoyed your service at Isaiah Nail Bar! We'd love to hear your feedback.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="https://share.google/OtbpRmUNfC1eA9kmZ" style="background-color: #e11d48; color: white; padding: 12px 24px; border-radius: 9999px; text-decoration: none; font-weight: bold; display: inline-block;">
            Leave a Review on Google
        </a>
    </div>

    <p style="text-align: center; color: #6b7280; font-size: 0.875rem;">
        Or reply to this email to let us know how we can improve.
    </p>

    <p style="margin-top: 30px;">Thank you,<br>Isaiah Nail Bar Team</p>
</body>
</html>
