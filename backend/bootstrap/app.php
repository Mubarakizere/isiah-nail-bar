<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Http\Middleware\ApiTokenAuth;
use App\Console\Commands\AutoCancelUnpaidBookings;
use App\Console\Commands\SendBookingReminders;
use App\Console\Commands\SendRebookingReminders;
use App\Console\Commands\TestEmailCommand;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // ✅ Register standard Laravel middleware groups
        $middleware->web();
        $middleware->api();

        // ✅ Alias Spatie permissions middleware
        $middleware->alias([
            'role'       => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'api.auth'   => ApiTokenAuth::class,
        ]);
    })
    ->withCommands([
        AutoCancelUnpaidBookings::class,
        SendBookingReminders::class,
        SendRebookingReminders::class,
        TestEmailCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('bookings:send-reminders')
            ->everyMinute()
            ->withoutOverlapping(2);
        $schedule->command('bookings:auto-cancel')
            ->everyMinute()
            ->withoutOverlapping(2);
        $schedule->command('bookings:send-rebooking-reminders')
            ->dailyAt('10:00')
            ->withoutOverlapping(10);
        $schedule->command('queue:work --stop-when-empty --max-time=50')
            ->everyMinute()
            ->withoutOverlapping(2);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception customization if needed
    })
    ->create();
