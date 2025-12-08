@extends('layouts.dashboard')

@section('title', 'Providers Performance Overview')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-chart-bar text-primary me-1"></i> Providers Performance Overview
    </h2>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th><i class="ph ph-user"></i> Name</th>
                    <th>Total Bookings</th>
                    <th>Completed</th>
                    <th>% Completion Rate</th>
                    <th><i class="ph ph-currency-circle-dollar"></i> Revenue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providers as $p)
                <tr>
                    <td>{{ $p->name }}</td>
<td>{{ $p->total }}</td>
<td>{{ $p->completed }}</td>
<td>{{ $p->rate }}%</td>
<td>RWF {{ number_format($p->revenue) }}</td>

                    <td>
    <a href="{{ route('admin.providers.performance.single', $p->id) }}" class="btn btn-outline-dark btn-sm">
        <i class="ph ph-eye me-1"></i> View Report
    </a>
</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
