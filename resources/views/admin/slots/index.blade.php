@extends('layouts.dashboard')

@section('title', 'Manage Time Slots')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">
        <i class="ph ph-clock me-2"></i> Manage Provider Time Slots
    </h4>

    <!-- Filter Panel -->
    <form method="GET" action="{{ route('admin.slots.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-5">
            <label for="provider_id" class="form-label fw-semibold">Select Provider</label>
            <select name="provider_id" id="provider_id" class="form-select shadow-sm" required>
                <option value="">-- Choose Provider --</option>
                @foreach ($providers as $prov)
                    <option value="{{ $prov->id }}" {{ $providerId == $prov->id ? 'selected' : '' }}>
                        {{ $prov->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="date" class="form-label fw-semibold">Choose Date</label>
            <input type="date" name="date" id="date" class="form-control shadow-sm" value="{{ $selectedDate }}">
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                <i class="ph ph-magnifying-glass me-1"></i> View Slots
            </button>
        </div>
    </form>

    @if ($provider && count($slots))
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light fw-semibold">
                Time Slots for <strong>{{ $provider->name }}</strong> on
                <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('l, M j, Y') }}</strong>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($slots as $slot)
                        <div class="col">
                            <div class="p-3 rounded shadow-sm text-center border
                                {{ $slot['status'] === 'blocked' ? 'bg-danger bg-opacity-10 text-danger' :
                                   ($slot['status'] === 'fully_booked' ? 'bg-light text-muted' : 'bg-white') }}">

                                <h6 class="fw-bold mb-1">{{ $slot['formatted'] }}</h6>
                                <div class="mb-2">
                                    @if ($slot['status'] === 'blocked')
                                        <span class="badge bg-danger">Blocked</span>
                                    @elseif ($slot['status'] === 'fully_booked')
                                        <span class="badge bg-secondary">Fully Booked</span>
                                    @else
                                        <span class="badge bg-success">{{ 3 - $slot['count'] }} spot(s) left</span>
                                    @endif
                                </div>

                                {{-- Buttons --}}
                                @if ($slot['status'] === 'blocked')
                                    <form method="POST" action="{{ route('admin.slots.unblock') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                                        <input type="hidden" name="time" value="{{ $slot['time'] }}">
                                        <button class="btn btn-sm btn-outline-danger w-100">
                                            <i class="ph ph-lock-open me-1"></i> Unblock
                                        </button>
                                    </form>
                                @elseif ($slot['status'] !== 'fully_booked')
                                    <form method="POST" action="{{ route('admin.slots.block') }}">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                                        <input type="hidden" name="time" value="{{ $slot['time'] }}">
                                        <button class="btn btn-sm btn-outline-warning w-100">
                                            <i class="ph ph-ban me-1"></i> Block
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($provider)
        <div class="alert alert-warning mt-4">
            No working hours set for <strong>{{ $provider->name }}</strong> on this day or it is marked as off/holiday.
        </div>
    @endif
</div>
@endsection
