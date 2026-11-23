<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class AdminMessageApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'unread' => ['nullable', 'boolean'],
        ]);

        $query = Contact::query();
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $messages = $query
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $messages->getCollection()
                ->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'message' => $contact->message,
                    'created_at' => $contact->created_at?->toISOString(),
                    'is_read' => $contact->read_at !== null,
                ])
                ->values(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'unread_count' => Contact::whereNull('read_at')->count(),
            ],
        ]);
    }

    public function markRead(Contact $contact)
    {
        if ($contact->read_at === null) {
            $contact->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'is_read' => true,
        ]);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.',
        ]);
    }
}
