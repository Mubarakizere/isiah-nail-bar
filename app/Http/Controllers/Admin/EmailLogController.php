<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('booking')->latest('sent_at');

        // Filter by email type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by recipient email
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('recipient_email', 'like', '%' . $request->search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        $emailTypes = EmailLog::distinct()->pluck('email_type');
        $statuses = ['sent', 'failed', 'pending'];

        return view('admin.emails.index', compact('logs', 'emailTypes', 'statuses'));
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load('booking');
        return view('admin.emails.show', compact('emailLog'));
    }
}
