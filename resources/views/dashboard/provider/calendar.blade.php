@extends('layouts.dashboard')

@section('title', 'My Booking Calendar')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="ph ph-calendar-blank me-2 text-primary"></i> My Booking Calendar
        </h2>
        <a href="{{ route('dashboard.provider') }}" class="btn btn-outline-secondary btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <select id="filterService" class="form-select">
                <option value="">All Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->name }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select id="filterStatus" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('provider.calendar.ical') }}" class="btn btn-outline-primary">
                <i class="ph ph-export me-1"></i> Sync with Google Calendar
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="mb-3">
        <span class="badge bg-warning me-1">Pending</span>
        <span class="badge bg-info me-1">Accepted</span>
        <span class="badge bg-success me-1">Completed</span>
        <span class="badge bg-secondary">Cancelled</span>
    </div>

    <!-- Calendar -->
    <div id="calendar" class="bg-white p-3 shadow-sm rounded border"></div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    #calendar {
        min-height: 600px;
    }
    .fc-toolbar-title {
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendarEl = document.getElementById('calendar');
        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            editable: true,
            eventDurationEditable: false,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: '{{ route('provider.calendar.fetch') }}',
                method: 'GET',
                extraParams: function() {
                    return {
                        service: document.getElementById('filterService').value,
                        status: document.getElementById('filterStatus').value
                    };
                },
                failure: function () {
                    alert('Failed to load bookings.');
                }
            },
            eventDidMount: function(info) {
                // Tooltip
                new bootstrap.Tooltip(info.el, {
                    title: `${info.event.title} at ${new Date(info.event.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.open(info.event.url, "_blank");
                }
            },
            eventDrop: function(info) {
                const bookingId = info.event.id;
                const newDate = info.event.startStr.split('T')[0];
                const newTime = info.event.start.toTimeString().split(' ')[0];

                fetch(`/dashboard/provider/calendar/reschedule/${bookingId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ date: newDate, time: newTime })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Rescheduling failed: ' + data.message);
                        info.revert();
                    }
                })
                .catch(() => {
                    alert('Server error. Could not reschedule.');
                    info.revert();
                });
            }
        });

        calendar.render();

        document.getElementById('filterService').addEventListener('change', () => calendar.refetchEvents());
        document.getElementById('filterStatus').addEventListener('change', () => calendar.refetchEvents());
    });
</script>
@endpush
