@php
    $role = auth()->user()->getRoleNames()->first();
@endphp

<nav id="sidebar" class="sidebar p-3 d-md-flex flex-column">
    <div class="mb-4 text-center">
        <a href="{{ url('/') }}" class="text-white text-decoration-none fw-bold">
            <img src="{{ asset('storage/logo-white.png') }}" alt="Logo" style="height: 40px;"><br>
            <span>Isaiah Nail Bar</span>
        </a>
    </div>

    <ul class="nav flex-column gap-1">
        <li class="nav-item mb-2 ps-2">
            <small class="text-uppercase text-muted">Dashboard</small><br>
            <strong class="text-white">{{ ucfirst($role) }}</strong>
        </li>

        {{-- Admin Links --}}
        @if($role === 'admin')
    <li><a href="{{ route('dashboard.admin') }}" class="nav-link {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
        <i class="ph ph-house"></i> Dashboard</a></li>

    <li><a href="{{ route('dashboard.admin.bookings') }}" class="nav-link {{ request()->routeIs('dashboard.admin.bookings') ? 'active' : '' }}">
        <i class="ph ph-calendar"></i> Bookings</a></li>

    <li><a href="{{ route('dashboard.admin.reports') }}" class="nav-link">
        <i class="ph ph-chart-line-up"></i> Reports</a></li>

    <li><a href="{{ route('admin.providers.performance') }}" class="nav-link">
        <i class="ph ph-chart-bar"></i> Provider Performance</a></li>

    <li class="mt-4 mb-1 ps-2"><small class="text-uppercase text-secondary">Management</small></li>

    <li><a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <i class="ph ph-folder-notch"></i> Categories</a></li>

    <li><a href="{{ route('admin.tags.index') }}" class="nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
        <i class="ph ph-tag"></i> Tags</a></li>

    <li><a href="{{ route('admin.services.index') }}" class="nav-link">
        <i class="ph ph-scissors"></i> Services</a></li>

    <li><a href="{{ route('admin.providers.index') }}" class="nav-link">
        <i class="ph ph-users-three"></i> Providers</a></li>

    
    <li><a href="{{ route('admin.slots.index') }}" class="nav-link {{ request()->routeIs('admin.slots.*') ? 'active' : '' }}">
        <i class="ph ph-clock"></i> Slot Management</a></li>

    <li><a href="{{ route('admin.messages') }}" class="nav-link">
        <i class="ph ph-envelope-simple"></i> Messages</a></li>

    <li><a href="{{ route('admin.services.pending') }}" class="nav-link">
        <i class="ph ph-hourglass"></i> Pending Services</a></li>

    <li><a href="{{ route('admin.gallery-instagram.index') }}" class="nav-link {{ request()->routeIs('admin.gallery-instagram.*') ? 'active' : '' }}">
        <i class="ph ph-instagram-logo"></i> Gallery Upload</a></li>
<a href="{{ route('admin.webhooks.index') }}" 
   class="nav-link {{ request()->routeIs('admin.webhooks.*') ? 'active' : '' }}">
   <i class="ph ph-bell-ring"></i> Webhook Logs
</a>




        {{-- Provider Links --}}
        @elseif($role === 'provider')
            <li><a href="{{ route('dashboard.provider') }}" class="nav-link {{ request()->routeIs('dashboard.provider') ? 'active' : '' }}"><i class="ph ph-house"></i> Dashboard</a></li>
            <li><a href="{{ route('provider.services.index') }}" class="nav-link"><i class="ph ph-scissors"></i> My Services</a></li>
            <li><a href="{{ route('provider.reviews.index') }}" class="nav-link"><i class="ph ph-star"></i> My Reviews</a></li>
            <li><a href="{{ route('provider.working-hours.edit') }}" class="nav-link"><i class="ph ph-clock"></i> Working Hours</a></li>
            <li><a href="{{ route('provider.calendar') }}" class="nav-link"><i class="ph ph-calendar-check"></i> Calendar</a></li>
            <li><a href="{{ route('provider.notifications') }}" class="nav-link"><i class="ph ph-bell"></i> Notifications</a></li>

        {{-- Customer Links --}}
        @elseif($role === 'customer')
            <li><a href="{{ route('dashboard.customer') }}" class="nav-link"><i class="ph ph-calendar-check"></i> My Bookings</a></li>
            <li><a href="{{ route('customer.reviews.index') }}" class="nav-link"><i class="ph ph-star"></i> My Reviews</a></li>
            <li><a href="{{ route('customer.notifications') }}" class="nav-link"><i class="ph ph-bell"></i> Notifications</a></li>
        @endif

        <li class="mt-4 mb-1 ps-2"><small class="text-uppercase text-secondary">Account</small></li>
        <li><a href="{{ route('profile.edit') }}" class="nav-link"><i class="ph ph-user-circle"></i> Edit Profile</a></li>
        <li>
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button class="btn btn-outline-danger btn-sm w-100 mt-2"><i class="ph ph-sign-out me-2"></i> Logout</button>
            </form>
        </li>
    </ul>
</nav>
