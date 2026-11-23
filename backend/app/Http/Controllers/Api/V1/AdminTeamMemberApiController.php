<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTeamMemberApiController extends Controller
{
    public function index()
    {
        $members = TeamMember::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $members->getCollection()
                ->map(fn (TeamMember $member) => $this->serializeMember($member))
                ->values(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
            'stats' => [
                'total' => TeamMember::count(),
                'active' => TeamMember::where('active', true)->count(),
                'inactive' => TeamMember::where('active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $data = [
            'name' => trim($validated['name']),
            'role' => trim($validated['role']),
            'bio' => $validated['bio'] ?? null,
            'active' => $validated['active'] ?? true,
            'display_order' => $validated['display_order'] ?? 0,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team_members', 'public');
        }

        $member = TeamMember::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Team member created successfully.',
            'data' => $this->serializeMember($member),
        ], 201);
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $validated = $request->validate($this->rules());
        $data = [
            'name' => trim($validated['name']),
            'role' => trim($validated['role']),
            'bio' => $validated['bio'] ?? null,
            'active' => $validated['active'] ?? $teamMember->active,
            'display_order' => $validated['display_order'] ?? $teamMember->display_order,
        ];

        $oldPhoto = $teamMember->photo;
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team_members', 'public');
        }

        $teamMember->update($data);

        if (!empty($data['photo']) && $oldPhoto && $oldPhoto !== $data['photo']) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team member updated successfully.',
            'data' => $this->serializeMember($teamMember->fresh()),
        ]);
    }

    public function destroy(TeamMember $teamMember)
    {
        $photo = $teamMember->photo;
        $teamMember->delete();

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team member deleted successfully.',
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'active' => ['sometimes', 'boolean'],
            'display_order' => ['sometimes', 'integer'],
        ];
    }

    private function serializeMember(TeamMember $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'role' => $member->role,
            'bio' => $member->bio,
            'photo_url' => $member->photo
                ? Storage::disk('public')->url($member->photo)
                : null,
            'active' => (bool) $member->active,
            'display_order' => (int) $member->display_order,
            'created_at' => $member->created_at?->toISOString(),
        ];
    }
}
