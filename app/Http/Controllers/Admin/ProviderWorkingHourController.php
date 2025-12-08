<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;

class ProviderWorkingHourController extends Controller
{
    /**
     * Show working hours form for a specific provider (admin access).
     */
    public function edit($providerId)
    {
        $provider = Provider::findOrFail($providerId);

        $days = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];

        $workingHours = $provider->workingHours
            ? $provider->workingHours->keyBy('day_of_week')
            : collect();

        return view('admin.providers.working_hours', compact('provider', 'days', 'workingHours'));
    }

    /**
     * Update or create working hours for the selected provider.
     */
    public function update(Request $request, $providerId)
    {
        $provider = Provider::findOrFail($providerId);

        if (!$request->has('working_hours') || !is_array($request->working_hours)) {
            return back()->with('error', 'No working hours submitted.');
        }

        foreach ($request->working_hours as $dayIndex => $times) {
            ProviderWorkingHour::updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'day_of_week' => $dayIndex,
                ],
                [
                    'start_time'   => $times['start_time'] ?? null,
                    'end_time'     => $times['end_time'] ?? null,
                    'break_start'  => $times['break_start'] ?? null,
                    'break_end'    => $times['break_end'] ?? null,
                    'is_day_off'   => isset($times['is_day_off']) && $times['is_day_off'] == 1,
                    'is_holiday'   => isset($times['is_holiday']) && $times['is_holiday'] == 1,
                ]
            );
        }

        return redirect()->route('admin.providers.hours.edit', $provider->id)
                         ->with('success', 'Working hours updated successfully!');
    }
}
