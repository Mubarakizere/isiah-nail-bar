@extends('layouts.public')

@section('title', 'Choose Date & Time')

@section('content')
@include('partials.booking-progress', ['currentStep' => 3])

<div class="container my-5">
    <form method="POST" action="{{ route('booking.step3.submit') }}">
        @csrf
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 d-none d-md-block border-end pe-4" data-aos="fade-right">
                <div class="text-center text-md-start">
                    <img src="{{ asset('storage/images/date-icon.svg') }}" alt="Date Icon" style="width: 50px;" class="mb-3">
                    <h5 class="fw-bold">Select Date & Time</h5>
                    <p class="text-muted small">Choose a suitable date and available time slot for your booking.</p>
                    <hr>
                    <h6 class="fw-bold">Need Help?</h6>
                    <p class="text-muted small mb-0">Call us at</p>
                    <a href="tel:+250788421063" class="text-primary small">
                        <i class="fas fa-phone me-1"></i> 0788 421 063
                    </a>
                </div>
            </div>

            <!-- Main Selection -->
            <div class="col-md-6 col-12 py-3" data-aos="fade-up">
                <h5 class="fw-semibold mb-3">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> Choose a Date
                </h5>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @for ($i = 0; $i < 14; $i++)
                        @php
                            $day = \Carbon\Carbon::today()->addDays($i);
                            $isSelected = $selectedDate === $day->toDateString();
                        @endphp
                        <a href="{{ route('booking.step3', ['booking_date' => $day->toDateString()]) }}"
                           class="btn {{ $isSelected ? 'btn-primary text-white fw-bold' : 'btn-outline-secondary' }} px-3">
                            {{ $day->format('D, M j') }}
                        </a>
                        @if ($i == 6) <div class="w-100"></div> @endif
                    @endfor
                </div>

                <input type="hidden" name="booking_date" value="{{ $selectedDate }}">

                <h5 class="fw-semibold mb-3">
                    <i class="fas fa-clock text-primary me-2"></i>
                    Time Slots for {{ \Carbon\Carbon::parse($selectedDate)->format('F j') }}
                </h5>

                @if (empty($slotsWithStatus))
                    <div class="alert alert-light border text-center">
                        No available time slots for this day.
                    </div>
                @else
                    <div class="row row-cols-2 row-cols-md-3 g-3 mb-4">
                        @foreach ($slotsWithStatus as $slot)
                            <div class="col time-option">
                                <input type="radio"
                                       name="booking_time"
                                       id="time_{{ $slot['time'] }}"
                                       value="{{ $slot['time'] }}"
                                       {{ old('booking_time') == $slot['time'] ? 'checked' : '' }}
                                       {{ $slot['status'] !== 'available' ? 'disabled' : '' }}>
                                <label for="time_{{ $slot['time'] }}"
                                       class="btn w-100 slot-label
                                       {{ $slot['status'] === 'available' ? 'btn-outline-success' : 'btn-outline-danger text-muted' }}">
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $slot['time'])->format('h:i A') }}
                                    <br>
                                    <small>
                                        @if ($slot['status'] === 'blocked')
                                            Blocked
                                        @elseif ($slot['status'] === 'full')
                                            Fully Booked
                                        @elseif ($slot['status'] === 'past')
                                            Passed
                                        @else
                                            Available
                                        @endif
                                    </small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('booking.step2') }}" class="btn btn-outline-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary px-4">Continue</button>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="col-md-3 d-none d-md-block border-start ps-4 bg-light" data-aos="fade-left">
                <div class="border rounded p-3 shadow-sm bg-white">
                    <h6 class="fw-bold mb-3">Booking Summary</h6>

                    @foreach ($services as $srv)
                        <p class="mb-1"><i class="fas fa-concierge-bell me-1 text-muted"></i> {{ $srv->name }}</p>
                    @endforeach

                    <hr class="my-2">
                    <p class="mb-1"><strong>Provider:</strong> {{ $provider->name }}</p>
                    <p class="mb-1"><strong>Customer:</strong> {{ auth()->user()->name ?? 'Guest' }}</p>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Total Price:</strong> RWF {{ number_format($services->sum('price')) }}</p>
                    <p class="mb-0"><strong>Total Duration:</strong> {{ $services->sum('duration_minutes') }} mins</p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .time-option input[type="radio"] {
        display: none;
    }

    .time-option input[type="radio"]:checked + label {
        background-color: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd;
    }

    .btn-outline-success:hover {
        background-color: #d1f1e0;
        border-color: #20c997;
        color: #198754;
    }

    .slot-label {
        text-align: center;
        padding: 10px;
    }

    .slot-label small {
        display: block;
        font-size: 0.75rem;
        margin-top: 4px;
    }
</style>
@endpush
