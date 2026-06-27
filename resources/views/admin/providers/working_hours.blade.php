@extends('layouts.dashboard')

@section('title', 'Working Hours - ' . $provider->name)

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    {{-- Header Section --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-2">Working Hours</h1>
            <p class="text-gray-500 text-lg">Manage schedule for <strong class="text-indigo-600">{{ $provider->name }}</strong></p>
        </div>
        <a href="{{ route('admin.providers.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 border border-gray-200 shadow-sm transition-all duration-300">
            <i class="ph ph-arrow-left mr-2"></i>Back to Providers
        </a>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-100 rounded-2xl p-5 mb-8 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph ph-warning-circle text-xl text-red-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-100 rounded-2xl p-5 mb-8 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="ph ph-check-circle text-xl text-green-600"></i>
            </div>
            <p class="text-green-800 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.providers.hours.update', $provider->id) }}">
        @csrf

        {{-- Global Tools --}}
        <div class="mb-6 flex items-center justify-between bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100">
            <div class="flex items-center gap-3">
                <i class="ph ph-info text-indigo-500 text-xl"></i>
                <p class="text-sm text-indigo-900">Set the daily schedule. Use <strong>Copy/Paste</strong> to quickly duplicate hours.</p>
            </div>
            <button type="submit" 
                    class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-black shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 flex items-center">
                <i class="ph ph-floppy-disk mr-2"></i>Save All
            </button>
        </div>

        {{-- Days Accordion --}}
        <div class="space-y-4 mb-8">
            @foreach($days as $dayIndex => $dayName)
                @php $hour = $workingHours[$dayIndex] ?? null; @endphp
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:border-indigo-100 hover:shadow-md">
                    {{-- Day Header --}}
                    <div class="px-6 py-5 bg-gray-50/80 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center shadow-sm text-indigo-600 font-bold">
                                {{ substr(ucfirst($dayName), 0, 1) }}
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ ucfirst($dayName) }}</h3>
                            
                            <div class="flex gap-2 ml-2">
                                <span class="status-badge-off {{ $hour && $hour->is_day_off ? '' : 'hidden' }} px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg border border-gray-200 flex items-center"><i class="ph ph-moon mr-1"></i>Day Off</span>
                                <span class="status-badge-holiday {{ $hour && $hour->is_holiday ? '' : 'hidden' }} px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg border border-amber-200 flex items-center"><i class="ph ph-sun mr-1"></i>Holiday</span>
                            </div>
                        </div>

                        {{-- Day Actions --}}
                        <div class="flex items-center gap-2">
                            <button type="button" class="copy-btn px-4 py-2 bg-white text-indigo-600 border border-indigo-100 text-sm font-bold rounded-lg hover:bg-indigo-50 transition-colors duration-200 shadow-sm flex items-center" data-tippy-content="Copy these hours">
                                <i class="ph ph-copy mr-1.5"></i>Copy
                            </button>
                            <button type="button" class="paste-btn px-4 py-2 bg-white text-green-600 border border-green-100 text-sm font-bold rounded-lg hover:bg-green-50 transition-colors duration-200 shadow-sm flex items-center" data-tippy-content="Paste copied hours">
                                <i class="ph ph-clipboard mr-1.5"></i>Paste
                            </button>
                        </div>
                    </div>

                    {{-- Day Content --}}
                    <div class="p-6 working-day-row relative {{ $hour && $hour->is_day_off ? 'bg-gray-50/50' : '' }}" data-day="{{ $dayIndex }}">
                        
                        {{-- Disabled Overlay --}}
                        <div class="disabled-overlay absolute inset-0 bg-gray-50/60 backdrop-blur-[1px] z-10 {{ $hour && $hour->is_day_off ? '' : 'hidden' }}"></div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 relative z-0">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center"><i class="ph ph-clock mr-1.5 text-gray-400"></i>Start Time</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][start_time]"
                                       class="start-time w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                       value="{{ $hour->start_time ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'readonly tabindex="-1"' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center"><i class="ph ph-clock-afternoon mr-1.5 text-gray-400"></i>End Time</label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][end_time]"
                                       class="end-time w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                       value="{{ $hour->end_time ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'readonly tabindex="-1"' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center"><i class="ph ph-coffee mr-1.5 text-gray-400"></i>Break Start <span class="text-gray-400 font-medium ml-1 text-xs">(Opt)</span></label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][break_start]"
                                       class="break-start w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                       value="{{ $hour->break_start ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'readonly tabindex="-1"' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center"><i class="ph ph-coffee mr-1.5 text-gray-400"></i>Break End <span class="text-gray-400 font-medium ml-1 text-xs">(Opt)</span></label>
                                <input type="time"
                                       name="working_hours[{{ $dayIndex }}][break_end]"
                                       class="break-end w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                       value="{{ $hour->break_end ?? '' }}"
                                       {{ $hour && $hour->is_day_off ? 'readonly tabindex="-1"' : '' }}>
                            </div>
                        </div>

                        {{-- Toggles --}}
                        <div class="flex flex-wrap items-center gap-3 relative z-20">
                            <span class="text-sm font-bold text-gray-700 mr-2">Status:</span>
                            
                            <button type="button" class="toggle-disable-btn px-4 py-2 border text-sm font-bold rounded-xl transition-all duration-200 flex items-center shadow-sm {{ $hour && $hour->is_day_off ? 'bg-gray-800 text-white border-gray-800 hover:bg-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                <i class="ph ph-prohibit mr-1.5 text-lg"></i>Day Off
                            </button>

                            <button type="button" class="toggle-holiday-btn px-4 py-2 border text-sm font-bold rounded-xl transition-all duration-200 flex items-center shadow-sm {{ $hour && $hour->is_holiday ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                <i class="ph ph-sun mr-1.5 text-lg"></i>Holiday
                            </button>
                        </div>

                        <input type="hidden" name="working_hours[{{ $dayIndex }}][is_day_off]" class="is-day-off" value="{{ $hour->is_day_off ?? 0 }}">
                        <input type="hidden" name="working_hours[{{ $dayIndex }}][is_holiday]" class="is-holiday" value="{{ $hour->is_holiday ?? 0 }}">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom Submit --}}
        <div class="px-8 py-5 bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('admin.providers.index') }}" 
               class="w-full sm:w-auto px-6 py-3.5 bg-white text-gray-700 font-bold rounded-xl hover:bg-gray-50 border border-gray-200 shadow-sm transition-all duration-300 text-center">
                Cancel
            </a>
            <button type="submit" 
                    class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="ph ph-floppy-disk mr-2 text-xl"></i>Save Working Hours
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    if(typeof tippy !== 'undefined') {
        tippy('[data-tippy-content]', {
            theme: 'light',
            animation: 'scale',
            placement: 'top',
        });
    }

    let clipboard = {};

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.bg-white').querySelector('.working-day-row');
            clipboard = {
                start: row.querySelector('.start-time').value,
                end: row.querySelector('.end-time').value,
                breakStart: row.querySelector('.break-start').value,
                breakEnd: row.querySelector('.break-end').value
            };
            
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="ph ph-check mr-1.5"></i>Copied!';
            this.classList.replace('text-indigo-600', 'text-green-600');
            this.classList.replace('border-indigo-100', 'border-green-200');
            this.classList.replace('hover:bg-indigo-50', 'hover:bg-green-50');
            
            setTimeout(() => {
                this.innerHTML = originalHtml;
                this.classList.replace('text-green-600', 'text-indigo-600');
                this.classList.replace('border-green-200', 'border-indigo-100');
                this.classList.replace('hover:bg-green-50', 'hover:bg-indigo-50');
            }, 2000);
        });
    });

    document.querySelectorAll('.paste-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!clipboard.start && !clipboard.end) {
                alert("No copied time found. Please copy times first.");
                return;
            }
            const row = this.closest('.bg-white').querySelector('.working-day-row');
            
            // Only paste if not a day off
            const isDayOff = row.querySelector('.is-day-off').value == 1;
            if (isDayOff) {
                alert("Cannot paste times into a Day Off. Toggle Day Off first.");
                return;
            }

            row.querySelector('.start-time').value = clipboard.start || '';
            row.querySelector('.end-time').value = clipboard.end || '';
            row.querySelector('.break-start').value = clipboard.breakStart || '';
            row.querySelector('.break-end').value = clipboard.breakEnd || '';
            
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="ph ph-check mr-1.5"></i>Pasted!';
            setTimeout(() => {
                this.innerHTML = originalHtml;
            }, 2000);
        });
    });

    document.querySelectorAll('.toggle-disable-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            const inputs = row.querySelectorAll('input[type="time"]');
            const hidden = row.querySelector('.is-day-off');
            const overlay = row.querySelector('.disabled-overlay');
            const headerBadge = this.closest('.bg-white').querySelector('.status-badge-off');
            
            const isDisabled = hidden.value == 0; // Toggling to Disabled

            hidden.value = isDisabled ? 1 : 0;

            if (isDisabled) {
                inputs.forEach(input => {
                    input.setAttribute('readonly', 'readonly');
                    input.setAttribute('tabindex', '-1');
                });
                overlay.classList.remove('hidden');
                row.classList.add('bg-gray-50/50');
                headerBadge.classList.remove('hidden');

                this.classList.remove('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
                this.classList.add('bg-gray-800', 'text-white', 'border-gray-800', 'hover:bg-gray-900');
            } else {
                inputs.forEach(input => {
                    input.removeAttribute('readonly');
                    input.removeAttribute('tabindex');
                });
                overlay.classList.add('hidden');
                row.classList.remove('bg-gray-50/50');
                headerBadge.classList.add('hidden');

                this.classList.remove('bg-gray-800', 'text-white', 'border-gray-800', 'hover:bg-gray-900');
                this.classList.add('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
            }
        });
    });

    document.querySelectorAll('.toggle-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.working-day-row');
            const hidden = row.querySelector('.is-holiday');
            const headerBadge = this.closest('.bg-white').querySelector('.status-badge-holiday');
            
            const isHoliday = hidden.value == 0;

            hidden.value = isHoliday ? 1 : 0;

            if (isHoliday) {
                headerBadge.classList.remove('hidden');
                
                this.classList.remove('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
                this.classList.add('bg-amber-500', 'text-white', 'border-amber-500', 'hover:bg-amber-600');
            } else {
                headerBadge.classList.add('hidden');

                this.classList.remove('bg-amber-500', 'text-white', 'border-amber-500', 'hover:bg-amber-600');
                this.classList.add('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
            }
        });
    });
</script>
@endpush
