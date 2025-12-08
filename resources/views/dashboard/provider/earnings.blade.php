@extends('layouts.dashboard')

@section('title', 'Earnings Overview')

@section('content')
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">📈 Weekly Earnings Overview</h2>

    <div class="row mb-4 text-center">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Total Bookings</h5>
                    <p class="fs-4 fw-bold">{{ $totalBookings }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <p class="fs-4 fw-bold">RWF {{ number_format($totalRevenue) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Completion Rate</h5>
                    <p class="fs-4 fw-bold">{{ $completionRate }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-4">
        <h5 class="mb-3">📊 Revenue Chart (Past 7 Days)</h5>
        <img src="data:image/png;base64,{{ $chart }}" class="img-fluid rounded" alt="Earnings Chart">
    </div>
</div>
@endsection
