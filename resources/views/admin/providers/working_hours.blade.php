@extends('layouts.dashboard')

@section('title', 'Edit Working Hours for ' . $provider->name)

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Working Hours</h1>
        <p class="text-gray-600">Manage schedule for <strong>{{ $provider->name }}</strong></p>
    </div>

    <form method="POST" action="{{ route('admin.providers.hours.update', $provider->id) }}">
        @csrf

        {{-- Days Accordion --}}
        <div class="space-y-3 mb-6">
            @foreach($days as $dayIndex => $dayName)
                @php $hour = $workingHours[$dayIndex] ?? null; @endphp
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    {{-- Day Header --}}
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="font-bold text-gray-900">{{ ucfirst($dayName) }}</h3>
                            @if($hour && $hour->is_day_off)
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-bold rounded-full">Day Off</span>
                            @endif
                            @if($hour && $hour->is_holiday)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">Holiday</span>
                            @endif
                        </div>
                    </div>

                    {{-- Day Content --}}
                    <div class="p-6 working-day-row" data-day="{{ $dayIndex }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][start_time]"
                                       class="start-time w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none"
                                       value="{{ $hour->start_time ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][end_time]"
                                       class="end-time w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none"
                                       value="{{ $hour->end_time ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Break Start</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][break_start]"
                                       class="break-start w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none"
                                       value="{{ $hour->break_start ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Break End</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][break_end]"
                                       class="break-end w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none"
                                       value="{{ $hour->break_end ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'disabled' : '' }}>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="copy-btn px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition">
                                <i class="ph ph-copy mr-1"></i>Copy
                            </button>
                            <button type="button" class="paste-btn px-3 py-1.5 bg-green-100 text-green-700 text-sm font-semibold rounded-lg hover:bg-green-200 transition">
                                <i class="ph ph-clipboard mr-1"></i>Paste
                            </button>
                            <button type="button" class="toggle-holiday-btn px-3 py-1.5 {{ $hour && $hour->is_holiday ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-700' }} text-sm font-semibold rounded-lg hover:bg-yellow-200 transition">
                                <i class="ph ph-sun mr-1"></i>Holiday
                            </button>
                            <button type="button" class="toggle-disable-btn px-3 py-1.5 {{ $hour && $hour->is_day_off ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700' }} text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                                <i class="ph ph-prohibit mr-1"></i>Day Off
                            </button>
                        </div>

                        <input type="hidden" name="working_hours[{{ $dayIndex }}][is_day_off]" class="is-day-off" value="{{ $hour->is_day_off ?? 0 }}">
                        <input type="hidden" name="working_hours[{{ $dayIndex }}][is_holiday]" class="is-holiday" value="{{ $hour->is_holiday ?? 0 }}">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Submit Buttons --}}
        <div class="flex gap-3">
            <a href="{{ route('admin.providers.index') }}" 
               class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                Back
            </a>
            <button type="submit" 
                    class="px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                <i class="ph ph-check-circle mr-2"></i>Save Working Hours
            </button>
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
            
            // Visual feedback
            this.innerHTML = '<i class="ph ph-check mr-1"></i>Copied!';
            setTimeout(() => {
                this.innerHTML = '<i class="ph ph-copy mr-1"></i>Copy';
            }, 2000);
        });
    });

    document.querySelectorAll('.paste-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!clipboard.start || !clipboard.end) {
                alert("No copied time found. Please copy times first.");
                return;
            }
            const row = this.closest('.working-day-row');
            row.querySelector('.start-time').value = clipboard.start;
            row.querySelector('.end-time').value = clipboard.end;
            row.querySelector('.break-start').value = clipboard.breakStart;
            row.querySelector('.break-end').value = clipboard.breakEnd;
            
            // Visual feedback
            this.innerHTML = '<i class="ph ph-check mr-1"></i>Pasted!';
            setTimeout(() => {
                this.innerHTML = '<i class="ph ph-clipboard mr-1"></i>Paste';
            }, 2000);
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

            if (isDisabled) {
                this.classList.remove('bg-gray-100', 'text-gray-700');
                this.classList.add('bg-gray-600', 'text-white');
            } else {
                this.classList.remove('bg-gray-600', 'text-white');
                this.classList.add('bg-gray-100', 'text-gray-700');
            }
        });
    });

    document.querySelectorAll('.toggle-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            const hidden = row.querySelector('.is-holiday');
            const isHoliday = hidden.value == 0;

            hidden.value = isHoliday ? 1 : 0;

            if (isHoliday) {
                this.classList.remove('bg-yellow-100', 'text-yellow-700');
                this.classList.add('bg-yellow-500', 'text-white');
            } else {
                this.classList.remove('bg-yellow-500', 'text-white');
                this.classList.add('bg-yellow-100', 'text-yellow-700');
            }
        });
    });
</script>
@endpush
