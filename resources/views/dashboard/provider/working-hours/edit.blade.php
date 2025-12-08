@extends('layouts.dashboard')

@section('title', 'Edit Working Hours')

@section('content')
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">🕒 My Working Hours</h2>

    <form method="POST" action="{{ route('provider.working-hours.update') }}">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dayMap = [
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday',
                            0 => 'Sunday',
                        ];
                    @endphp

                    @foreach($dayMap as $dayIndex => $dayName)
                        @php
                            $existing = $hours->firstWhere('day_of_week', $dayIndex);
                        @endphp
                        <tr>
                            <td>{{ $dayName }}</td>
                            <td>
                                <input type="time" name="working_hours[{{ $dayIndex }}][start_time]"
                                       value="{{ $existing->start_time ?? '08:00' }}" class="form-control">
                            </td>
                            <td>
                                <input type="time" name="working_hours[{{ $dayIndex }}][end_time]"
                                       value="{{ $existing->end_time ?? '17:00' }}" class="form-control">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-3">💾 Save Working Hours</button>
    </form>
</div>
@endsection
