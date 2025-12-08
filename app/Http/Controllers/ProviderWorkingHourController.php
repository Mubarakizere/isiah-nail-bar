<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProviderWorkingHour;

class ProviderWorkingHourController extends Controller
{
    /**
     * Show the working hours form for the logged-in provider.
     */
    public function edit()
    {
        $provider = Auth::user()->provider;
        $hours = $provider->workingHours ?? collect();

        return view('dashboard.provider.working_hours', compact('hours'));
    }

    /**
     * Update or create the working hours for the logged-in provider.
     */
    public function update(Request $request)
    {
        $provider = Auth::user()->provider;

        foreach ($request->working_hours as $dayIndex => $times) {
            if (!empty($times['start_time']) && !empty($times['end_time'])) {
                ProviderWorkingHour::updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'day_of_week' => $dayIndex,
                    ],
                    [
                        'start_time' => $times['start_time'],
                        'end_time' => $times['end_time'],
                    ]
                );
            }
        }

        return redirect()->route('provider.working-hours.edit')->with('success', 'Working hours updated!');
    }
}
