@extends('layouts.dashboard')

@section('title', 'Edit Working Hours for ' . $provider->name)

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Edit Working Hours — {{ $provider->name }}</h2>

    <form method="POST" action="{{ route('admin.providers.hours.update', $provider->id) }}">
        @csrf

        <div class="accordion" id="workingHoursAccordion">
            @foreach($days as $dayIndex => $dayName)
                @php $hour = $workingHours[$dayIndex] ?? null; @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $dayIndex }}">
                        <button class="accordion-button {{ $dayIndex !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $dayIndex }}" aria-expanded="{{ $dayIndex === 0 ? 'true' : 'false' }}" aria-controls="collapse-{{ $dayIndex }}">
                            {{ ucfirst($dayName) }}
                            @if($hour && $hour->is_day_off)
                                <span class="badge bg-secondary ms-2">Day Off</span>
                            @endif
                            @if($hour && $hour->is_holiday)
                                <span class="badge bg-warning text-dark ms-2">Holiday</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse-{{ $dayIndex }}" class="accordion-collapse collapse {{ $dayIndex === 0 ? 'show' : '' }}" aria-labelledby="heading-{{ $dayIndex }}" data-bs-parent="#workingHoursAccordion">
                        <div class="accordion-body">
                            <div class="row g-3 align-items-center working-day-row" data-day="{{ $dayIndex }}">
                                <div class="col-md-3">
                                    <label class="form-label">Start Time</label>
                                    <input type="time"
                                           name="working_hours[{{ $dayIndex }}][start_time]"
                                           class="form-control start-time"
                                           value="{{ $hour->start_time ?? '' }}"
                                           {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time"
                                           name="working_hours[{{ $dayIndex }}][end_time]"
                                           class="form-control end-time"
                                           value="{{ $hour->end_time ?? '' }}"
                                           {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Break Start</label>
                                    <input type="time"
                                           name="working_hours[{{ $dayIndex }}][break_start]"
                                           class="form-control break-start"
                                           value="{{ $hour->break_start ?? '' }}"
                                           {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Break End</label>
                                    <input type="time"
                                           name="working_hours[{{ $dayIndex }}][break_end]"
                                           class="form-control break-end"
                                           value="{{ $hour->break_end ?? '' }}"
                                           {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm copy-btn">Copy Times</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm paste-btn">Paste Times</button>
                                    <button type="button" class="btn btn-outline-warning btn-sm toggle-holiday-btn">Mark as Holiday</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm toggle-disable-btn">Mark as Day Off</button>
                                </div>

                                <input type="hidden" name="working_hours[{{ $dayIndex }}][is_day_off]" class="is-day-off" value="{{ $hour->is_day_off ?? 0 }}">
                                <input type="hidden" name="working_hours[{{ $dayIndex }}][is_holiday]" class="is-holiday" value="{{ $hour->is_holiday ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary">Back</a>
            <button type="submit" class="btn btn-success px-4">Save Working Hours</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let clipboard = {};

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            clipboard = {
                start: row.querySelector('.start-time').value,
                end: row.querySelector('.end-time').value,
                breakStart: row.querySelector('.break-start').value,
                breakEnd: row.querySelector('.break-end').value
            };
            alert("Times copied.");
        });
    });

    document.querySelectorAll('.paste-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!clipboard.start || !clipboard.end) return alert("No copied time found.");
            const row = this.closest('.working-day-row');
            row.querySelector('.start-time').value = clipboard.start;
            row.querySelector('.end-time').value = clipboard.end;
            row.querySelector('.break-start').value = clipboard.breakStart;
            row.querySelector('.break-end').value = clipboard.breakEnd;
        });
    });

    document.querySelectorAll('.toggle-disable-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            const inputs = row.querySelectorAll('input[type="time"]');
            const hidden = row.querySelector('.is-day-off');
            const isDisabled = hidden.value == 0;

            inputs.forEach(input => input.disabled = isDisabled);
            hidden.value = isDisabled ? 1 : 0;

            this.classList.toggle('btn-outline-danger');
            this.classList.toggle('btn-danger');
        });
    });

    document.querySelectorAll('.toggle-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            const hidden = row.querySelector('.is-holiday');
            const isHoliday = hidden.value == 0;

            hidden.value = isHoliday ? 1 : 0;
            this.classList.toggle('btn-outline-warning');
            this.classList.toggle('btn-warning');
        });
    });
</script>
@endpush
