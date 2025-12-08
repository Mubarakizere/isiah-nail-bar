<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Provider Appointment Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>🔔 Upcoming Appointment Reminder</h2>

    <p>Hi {{ $booking->provider->name ?? 'Provider' }},</p>

    <p>You have an upcoming appointment scheduled with <strong>{{ $booking->customer->user->name ?? 'a client' }}</strong>.</p>

    <ul>
        <li><strong>Services:</strong> {{ $booking->services->pluck('name')->implode(', ') }}</li>
        <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->date)->format('F j, Y') }}</li>
        <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</li>
    </ul>

    <p>Please make sure you're ready on time. Thank you for providing excellent service through Isaiah Nail Bar!</p>

    <p style="margin-top: 20px;">– Isaiah Nail Bar Team</p>
</body>
</html>
