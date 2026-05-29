<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('dashboard.admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_alert_emails' => 'nullable|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'booking_alert_emails'],
            ['value' => $request->booking_alert_emails]
        );

        return back()->with('success', 'Settings updated successfully.');
    }
}
