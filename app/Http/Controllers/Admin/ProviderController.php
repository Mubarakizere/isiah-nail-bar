<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::latest()->paginate(10);
        return view('admin.providers.index', compact('providers'));
    }

    public function create()
    {
        $services = Service::all();
        return view('admin.providers.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $data = $request->only('name', 'email', 'phone', 'bio');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('providers', 'public');
        }

        $provider = Provider::create($data);
        $provider->services()->sync($request->input('services', []));

        return redirect()->route('admin.providers.index')->with('success', 'Provider added successfully.');
    }

    public function edit(Provider $provider)
    {
        $services = Service::all();
        return view('admin.providers.edit', compact('provider', 'services'));
    }

    public function update(Request $request, Provider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $data = $request->only('name', 'email', 'phone', 'bio');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('providers', 'public');
        }

        $provider->update($data);
        $provider->services()->sync($request->input('services', []));

        return redirect()->route('admin.providers.index')->with('success', 'Provider updated successfully.');
    }

    public function destroy($id)
{
    $provider = Provider::findOrFail($id);

    // Optional: Detach relationships like services, bookings
    $provider->services()->detach();

    $provider->delete();

    return redirect()->route('admin.providers.index')->with('success', 'Provider deleted successfully.');
}


    public function approve($id)
    {
        $provider = Provider::findOrFail($id);
        $provider->active = true;
        $provider->save();

        return redirect()->back()->with('success', 'Provider approved successfully.');
    }

    public function decline($id)
    {
        $provider = Provider::findOrFail($id);
        $provider->active = false;
        $provider->save();

        return redirect()->back()->with('success', 'Provider declined successfully.');
    }

    public function performance(Provider $provider)
    {
        $totalBookings = Booking::where('provider_id', $provider->id)->count();
        $completedBookings = Booking::where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->count();

        $completionRate = $totalBookings > 0
            ? round(($completedBookings / $totalBookings) * 100, 1)
            : 0;

        $totalRevenue = DB::table('bookings')
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->where('bookings.provider_id', $provider->id)
            ->where('payments.status', 'paid')
            ->sum('payments.amount');

        $start = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $rawData = DB::table('bookings')
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->where('bookings.provider_id', $provider->id)
            ->whereBetween('bookings.date', [$start, $end])
            ->where('payments.status', 'paid')
            ->select(
                DB::raw('DATE(bookings.date) as date'),
                DB::raw("SUM(payments.amount) as total")
            )
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $label = Carbon::parse($date)->format('M d');
            $chartData->push([
                'date' => $label,
                'total' => (float) ($rawData[$date] ?? 0),
            ]);
        }

        return view('dashboard.admin.provider-performance', compact(
            'provider',
            'totalBookings',
            'completedBookings',
            'completionRate',
            'totalRevenue',
            'chartData'
        ));
    }

    public function allPerformance()
    {
        $providers = Provider::with('user')->get()->map(function ($provider) {
            $total = Booking::where('provider_id', $provider->id)->count();
            $completed = Booking::where('provider_id', $provider->id)->where('status', 'completed')->count();
            $revenue = DB::table('bookings')
                ->join('payments', 'bookings.id', '=', 'payments.booking_id')
                ->where('bookings.provider_id', $provider->id)
                ->where('payments.status', 'paid')
                ->sum('payments.amount');

            return (object)[
                'id' => $provider->id,
                'name' => $provider->user->name ?? '—',
                'total' => $total,
                'completed' => $completed,
                'rate' => $total ? round($completed / $total * 100, 1) : 0,
                'revenue' => $revenue,
            ];
        });

        return view('dashboard.admin.providers-performance', compact('providers'));
    }

    public function show($id)
    {
        $provider = Provider::with('user', 'bookings.services')->findOrFail($id);
        return view('dashboard.admin.providers.show', compact('provider'));
    }
}
