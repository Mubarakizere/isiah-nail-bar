@extends('layouts.dashboard')

@section('title', 'Customer Dashboard')

@section('content')
<div class="container">

    <h4 class="fw-bold mb-4">👋 Welcome back, {{ Auth::user()->name }}!</h4>
        <p class="text-muted">Here are your {{ $status }} bookings.</p>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    @if($bookings->isEmpty())
        <div class="alert alert-info text-center py-5">
            <h4>No bookings yet!</h4>
            <a href="{{ route('booking.step1') }}" class="btn btn-primary mt-3">➕ Book a Service</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>Provider</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr class="text-center">
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->service->name ?? 'N/A' }}</td>
                            <td>{{ $booking->provider->name ?? 'N/A' }}</td>
                            <td>{{ $booking->date }}</td>
                            <td>{{ $booking->time }}</td>
                            <td>
                                @if($booking->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($booking->status == 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($booking->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @elseif($booking->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('customer.booking.show', $booking->id) }}" class="btn btn-info btn-sm mb-1">View</a>

                                @if($booking->status == 'pending')
                                    <form action="{{ route('customer.booking.cancel', $booking->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {{ $bookings->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
