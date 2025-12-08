<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    /**
     * Determine if the provider can update the service.
     */
    public function update(User $user, Service $service): bool
    {
        return $user->role === 'provider'
            && $user->provider
            && $user->provider->id === $service->provider_id;
    }

    /**
     * Determine if the provider can delete the service.
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->role === 'provider'
            && $user->provider
            && $user->provider->id === $service->provider_id;
    }
}
