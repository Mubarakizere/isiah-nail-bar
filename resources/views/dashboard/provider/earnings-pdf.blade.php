<!DOCTYPE html>
<html>
<head>
    <title>Provider Earnings Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Earnings Report</h2>
    <p>Provider: {{ $provider->name }}</p>
    <p>Date: {{ $today }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Service</th>
                <th>Amount (RWF)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ number_format($row->price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 style="text-align: right; margin-top: 20px;">Total Revenue: RWF {{ number_format($total) }}</h4>
</body>
</html>
