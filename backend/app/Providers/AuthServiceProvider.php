<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Service;
use App\Policies\ServicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Service::class => ServicePolicy::class,
    ];
    public function boot(): void
    {
        Gate::policy(Service::class, ServicePolicy::class);
    }
}
