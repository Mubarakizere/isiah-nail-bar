<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ProviderCredentialsMail;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;
use App\Models\Service;
use App\Models\User;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminProviderApiController extends Controller
{
    private const WORKING_DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Provider::query()
            ->with(['services:id,name'])
            ->withCount(['services', 'bookings']);

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($providers) use ($term) {
                $providers->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $providers = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $providers->getCollection()
                ->map(fn (Provider $provider) => $this->serializeProvider($provider))
                ->values(),
            'meta' => [
                'current_page' => $providers->currentPage(),
                'last_page' => $providers->lastPage(),
                'per_page' => $providers->perPage(),
                'total' => $providers->total(),
            ],
            'stats' => [
                'total' => Provider::count(),
                'active' => Provider::where('active', true)->count(),
                'pending' => Provider::where('active', false)->count(),
            ],
            'services' => Service::query()->orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }

    public function workingHours(Provider $provider)
    {
        $savedHours = $provider->workingHours()
            ->orderByDesc('id')
            ->get()
            ->unique('day_of_week')
            ->keyBy(fn (ProviderWorkingHour $hour) => (int) $hour->day_of_week);

        $workingHours = collect(self::WORKING_DAYS)->map(function (string $dayName, int $dayOfWeek) use ($savedHours) {
            /** @var ProviderWorkingHour|null $hour */
            $hour = $savedHours->get($dayOfWeek);

            return [
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayName,
                'configured' => $hour !== null && (
                    (bool) $hour->is_day_off
                    || (bool) $hour->is_holiday
                    || ($hour->start_time && $hour->end_time)
                ),
                'start_time' => $hour?->start_time ? substr((string) $hour->start_time, 0, 5) : '',
                'end_time' => $hour?->end_time ? substr((string) $hour->end_time, 0, 5) : '',
                'break_start' => $hour?->break_start ? substr((string) $hour->break_start, 0, 5) : '',
                'break_end' => $hour?->break_end ? substr((string) $hour->break_end, 0, 5) : '',
                'is_day_off' => (bool) ($hour?->is_day_off ?? false),
                'is_holiday' => (bool) ($hour?->is_holiday ?? false),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => ['id' => $provider->id, 'name' => $provider->name],
                'working_hours' => $workingHours,
            ],
        ]);
    }

    public function updateWorkingHours(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'working_hours' => ['required', 'array', 'size:7'],
            'working_hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'working_hours.*.start_time' => ['nullable', 'date_format:H:i'],
            'working_hours.*.end_time' => ['nullable', 'date_format:H:i'],
            'working_hours.*.break_start' => ['nullable', 'date_format:H:i'],
            'working_hours.*.break_end' => ['nullable', 'date_format:H:i'],
            'working_hours.*.is_day_off' => ['required', 'boolean'],
            'working_hours.*.is_holiday' => ['required', 'boolean'],
        ]);

        foreach ($data['working_hours'] as $index => $hours) {
            $unavailable = (bool) $hours['is_day_off'] || (bool) $hours['is_holiday'];
            $start = $hours['start_time'] ?? null;
            $end = $hours['end_time'] ?? null;
            $breakStart = $hours['break_start'] ?? null;
            $breakEnd = $hours['break_end'] ?? null;

            if (!$unavailable && (!$start || !$end)) {
                throw ValidationException::withMessages([
                    "working_hours.{$index}.start_time" => self::WORKING_DAYS[(int) $hours['day_of_week']].' needs both opening and closing times, or must be marked unavailable.',
                ]);
            }
            if (!$unavailable && $start >= $end) {
                throw ValidationException::withMessages([
                    "working_hours.{$index}.end_time" => 'Closing time must be later than opening time.',
                ]);
            }
            if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                throw ValidationException::withMessages([
                    "working_hours.{$index}.break_end" => 'Enter both break times or leave both blank.',
                ]);
            }
            if ($breakStart && $breakEnd && (!$start || !$end || $breakStart >= $breakEnd || $breakStart < $start || $breakEnd > $end)) {
                throw ValidationException::withMessages([
                    "working_hours.{$index}.break_start" => 'The break must be inside the provider’s working hours.',
                ]);
            }
        }

        DB::transaction(function () use ($provider, $data) {
            Provider::query()->whereKey($provider->id)->lockForUpdate()->firstOrFail();

            foreach ($data['working_hours'] as $hours) {
                $dayOfWeek = (int) $hours['day_of_week'];
                $record = ProviderWorkingHour::query()
                    ->where('provider_id', $provider->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->orderByDesc('id')
                    ->first();

                $unavailable = (bool) $hours['is_day_off'] || (bool) $hours['is_holiday'];
                $values = [
                    'start_time' => $unavailable ? null : ($hours['start_time'] ?? null),
                    'end_time' => $unavailable ? null : ($hours['end_time'] ?? null),
                    'break_start' => $unavailable ? null : ($hours['break_start'] ?? null),
                    'break_end' => $unavailable ? null : ($hours['break_end'] ?? null),
                    'is_day_off' => (bool) $hours['is_day_off'],
                    'is_holiday' => (bool) $hours['is_holiday'],
                ];

                if ($record) {
                    $record->update($values);
                    ProviderWorkingHour::query()
                        ->where('provider_id', $provider->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('id', '!=', $record->id)
                        ->delete();
                } else {
                    ProviderWorkingHour::create($values + [
                        'provider_id' => $provider->id,
                        'day_of_week' => $dayOfWeek,
                    ]);
                }
            }
        });

        return $this->workingHours(Provider::query()->findOrFail($provider->id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $photoPath = null;

        try {
            $provider = DB::transaction(function () use ($request, $validated, &$photoPath) {
                $data = [
                    'name' => trim($validated['name']),
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ];

                $generatedPassword = null;
                if (!empty($data['email'])) {
                    $generatedPassword = $this->generateSecurePassword();
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($generatedPassword),
                    ]);

                    if (Role::where('name', 'provider')->exists()) {
                        $user->assignRole('provider');
                    }

                    $data['user_id'] = $user->id;
                }

                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('providers', 'public');
                    $data['photo'] = $photoPath;
                }

                $provider = Provider::create($data);
                $provider->services()->sync($validated['services'] ?? []);

                if ($generatedPassword !== null) {
                    Mail::to($provider->email)->send(
                        new ProviderCredentialsMail($provider, $generatedPassword)
                    );
                } else {
                    foreach (Provider::GENERAL_NOTIFICATION_EMAILS as $notifyEmail) {
                        Mail::to($notifyEmail)->send(
                            new ProviderCredentialsMail($provider, 'N/A — No email set')
                        );
                    }
                }

                return $provider;
            });

            $provider->load(['services:id,name'])->loadCount(['services', 'bookings']);

            return response()->json([
                'success' => true,
                'message' => 'Provider added successfully.',
                'data' => $this->serializeProvider($provider),
            ], 201);
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Provider creation failed: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create provider. Check the details and try again.',
            ], 500);
        }
    }

    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate($this->rules($provider));
        $oldPhoto = $provider->photo;

        $data = [
            'name' => trim($validated['name']),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('providers', 'public');
        }

        $provider->update($data);
        $provider->services()->sync($validated['services'] ?? []);

        if (!empty($data['photo']) && $oldPhoto && $oldPhoto !== $data['photo']) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $provider->load(['services:id,name'])->loadCount(['services', 'bookings']);

        return response()->json([
            'success' => true,
            'message' => 'Provider updated successfully.',
            'data' => $this->serializeProvider($provider),
        ]);
    }

    public function destroy(Provider $provider)
    {
        $provider->services()->detach();

        if ($provider->photo) {
            Storage::disk('public')->delete($provider->photo);
        }

        $provider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Provider deleted successfully.',
        ]);
    }

    public function approve(Provider $provider)
    {
        $provider->update(['active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Provider approved successfully.',
        ]);
    }

    public function decline(Provider $provider)
    {
        $provider->update(['active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Provider declined successfully.',
        ]);
    }

    private function rules(?Provider $provider = null): array
    {
        $providerEmailRule = Rule::unique('providers', 'email');
        if ($provider) {
            $providerEmailRule->ignore($provider->id);
        }

        $userEmailRule = Rule::unique('users', 'email');
        if ($provider?->user_id) {
            $userEmailRule->ignore($provider->user_id);
        }
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', $providerEmailRule, $userEmailRule],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:30720'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ];
    }

    private function generateSecurePassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        for ($index = 0; $index < $length; $index++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    private function serializeProvider(Provider $provider): array
    {
        return [
            'id' => $provider->id,
            'name' => $provider->name,
            'email' => $provider->email,
            'phone' => $provider->phone,
            'bio' => $provider->bio,
            'photo_url' => StorageUrl::format($provider->photo),
            'active' => (bool) $provider->active,
            'services' => $provider->services->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
            ])->values(),
            'services_count' => (int) ($provider->services_count ?? $provider->services->count()),
            'bookings_count' => (int) ($provider->bookings_count ?? 0),
            'created_at' => $provider->created_at?->toISOString(),
        ];
    }
}
