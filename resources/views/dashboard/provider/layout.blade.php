@extends('layouts.dashboard') {{-- Reuse base dashboard layout if you have it --}}

@section('title', 'Provider Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-white shadow-sm px-0 position-sticky min-vh-100" style="top: 0;">
            <div class="d-flex flex-column p-3">
                <h5 class="fw-bold mb-4 text-center text-primary">📋 My Dashboard</h5>

                <ul class="nav flex-column nav-pills gap-2">
                    <li class="nav-item">
                        <a href="{{ route('dashboard.provider') }}" class="nav-link {{ request()->routeIs('dashboard.provider') ? 'active' : '' }}">
                            📊 Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('provider.services.index') }}" class="nav-link {{ request()->routeIs('provider.services.*') ? 'active' : '' }}">
                            💼 My Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('provider.services.request') }}" class="nav-link {{ request()->routeIs('provider.services.request') ? 'active' : '' }}">
                            ➕ Request Service
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('provider.reviews.index') }}" class="nav-link {{ request()->routeIs('provider.reviews.index') ? 'active' : '' }}">
                            ⭐ My Reviews
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-danger w-100">🚪 Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 py-4 px-4">
            @yield('provider-content')
        </div>
    </div>
</div>
@endsection
