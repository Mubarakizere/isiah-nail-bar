@php
    $currentTitle = trim(View::getSections()['title'] ?? 'Dashboard');
    $unread = auth()->user()->unreadNotifications()->count();
    $role = auth()->user()->getRoleNames()->first();
@endphp

<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm px-4">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggler d-md-none" onclick="toggleSidebar()"><i class="ph ph-list fs-4"></i></button>
            <h5 class="mb-0 fw-semibold d-none d-md-block">{{ $currentTitle }}</h5>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route($role . '.notifications') }}" class="position-relative text-dark me-2">
                <i class="ph ph-bell fs-4"></i>
                @if($unread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unread }}
                    </span>
                @endif
            </a>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->profile_photo_url ?? asset('storage/images/default-user.png') }}" alt="avatar" class="profile-avatar me-2">
                    <strong>{{ auth()->user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Edit Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">@csrf
                            <button class="dropdown-item text-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
