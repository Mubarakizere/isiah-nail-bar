<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $teamMembers = TeamMember::orderBy('display_order')->paginate(10);
        return view('admin.team_members.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('admin.team_members.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:10240', // 10MB max
            'active' => 'boolean',
            'display_order' => 'integer',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('public/team_members');
            // Remove 'public/' from the path to store in database relative to storage/app/public
            $data['photo'] = str_replace('public/', '', $data['photo']);
        }

        TeamMember::create($data);

        return redirect()->route('admin.team_members.index')
            ->with('success', 'Team member created successfully.');
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team_members.edit', compact('teamMember'));
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:10240',
            'active' => 'boolean',
            'display_order' => 'integer',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teamMember->photo) {
                Storage::delete('public/' . $teamMember->photo);
            }
            
            $data['photo'] = $request->file('photo')->store('public/team_members');
            $data['photo'] = str_replace('public/', '', $data['photo']);
        }

        $teamMember->update($data);

        return redirect()->route('admin.team_members.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $teamMember)
    {
        if ($teamMember->photo) {
            Storage::delete('public/' . $teamMember->photo);
        }
        
        $teamMember->delete();

        return redirect()->route('admin.team_members.index')
            ->with('success', 'Team member deleted successfully.');
    }
}
