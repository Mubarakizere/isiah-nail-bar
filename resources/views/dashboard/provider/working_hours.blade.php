@extends('layouts.dashboard')

@section('title', 'My Working Hours')

@section('content')
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">🕒 My Working Hours</h2>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('provider.working-hours.update') }}" id="workingHoursForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Day</th>
                                <th>Enable</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Holiday</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                                0 => 'Sunday'
                            ] as $dayIndex => $dayName)
                                @php
                                    $existing = $hours->firstWhere('day_of_week', $dayIndex);
                                @endphp
                                <tr id="row-{{ $dayIndex }}">
                                    <td><strong>{{ $dayName }}</strong></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input enable-toggle" name="working_hours[{{ $dayIndex }}][enabled]" value="1"
                                            {{ $existing ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control start-time" name="working_hours[{{ $dayIndex }}][start_time]"
                                               value="{{ $existing->start_time ?? '' }}" {{ $existing ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control end-time" name="working_hours[{{ $dayIndex }}][end_time]"
                                               value="{{ $existing->end_time ?? '' }}" {{ $existing ? '' : 'disabled' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" name="working_hours[{{ $dayIndex }}][is_holiday]"
                                            {{ $existing && $existing->is_holiday ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-dark copy-btn" data-day="{{ $dayIndex }}">
                                            Copy
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary paste-btn" data-day="{{ $dayIndex }}">
                                            Paste
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    💾 Save Working Hours
                </button>
            </div>

            <!-- Calendar Preview -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="ph ph-calendar-check text-primary me-1"></i> Preview
                        </h5>
                        <div id="calendarPreview" class="small text-muted">
                            <p>This section will display a visual preview of working days and time slots.</p>
                            <ul id="previewList" class="list-unstyled"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const workingData = {};

    document.querySelectorAll('.enable-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input[type="time"], input[type="checkbox"][name*="is_holiday"]');
            inputs.forEach(input => input.disabled = !this.checked);
            updatePreview();
        });
    });

    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function () {
            const day = this.dataset.day;
            const row = document.querySelector(`#row-${day}`);
            const start = row.querySelector('.start-time').value;
            const end = row.querySelector('.end-time').value;
            if (!start || !end) return alert("Please set both start and end time to copy.");
            workingData.start = start;
            workingData.end = end;
            alert('Working hours copied!');
        });
    });

    document.querySelectorAll('.paste-btn').forEach(button => {
        button.addEventListener('click', function () {
            const day = this.dataset.day;
            if (!workingData.start || !workingData.end) return alert("Nothing to paste. Copy hours first.");
            const row = document.querySelector(`#row-${day}`);
            row.querySelector('.start-time').value = workingData.start;
            row.querySelector('.end-time').value = workingData.end;
            row.querySelector('.enable-toggle').checked = true;
            row.querySelectorAll('input[type="time"]').forEach(i => i.disabled = false);
            updatePreview();
        });
    });

    function updatePreview() {
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const preview = document.getElementById('previewList');
        preview.innerHTML = '';
        document.querySelectorAll('tbody tr').forEach(row => {
            const index = row.id.split('-')[1];
            const enabled = row.querySelector('.enable-toggle').checked;
            const start = row.querySelector('.start-time').value;
            const end = row.querySelector('.end-time').value;
            if (enabled && start && end) {
                preview.innerHTML += `<li><strong>${days[index]}</strong>: ${start} - ${end}</li>`;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection
