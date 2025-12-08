<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;

class WebhookLogController extends Controller
{
    public function index()
    {
        $logs = WebhookLog::latest()->paginate(20);

        return view('admin.webhooks.index', compact('logs'));
    }
}
