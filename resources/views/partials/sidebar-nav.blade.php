@php
    $role = auth()->user()->getRoleNames()->first();
@endphp

{{-- Admin Navigation --}}
@if($role === 'admin')
    <div x-data="{
        viewOpen: {{ request()->routeIs('dashboard.admin', 'dashboard.admin.bookings', 'dashboard.admin.reports', 'admin.providers.performance') ? 'true' : 'false' }},
        managementOpen: {{ request()->routeIs('admin.categories.*', 'admin.services.*', 'admin.providers.*', 'admin.team_members.*', 'admin.slots.*', 'admin.messages', 'admin.webmail.*', ' admin.gallery-instagram.*', 'admin.tags.*', 'admin.reviews.*') ? 'true' : 'false' }},
        toolsOpen: {{ request()->routeIs('admin.bookings.manual.*', 'admin.services.pending', 'admin.emails.*', 'admin.webhooks.*') ? 'true' : 'false' }}
    }" class="space-y-2">
        
        {{-- VIEW SECTION --}}
        <div class="border-b border-gray-800 pb-2">
            <button @click="viewOpen = !viewOpen" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-white transition">
                <span>View</span>
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': viewOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="viewOpen" x-collapse class="mt-1 space-y-0.5">
                <a href="{{ route('dashboard.admin') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard.admin') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-house text-base"></i>
                    <span>Overview</span>
                </a>

                <a href="{{ route('dashboard.admin.bookings') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard.admin.bookings') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-calendar text-base"></i>
                    <span>Bookings</span>
                </a>

                <a href="{{ route('dashboard.admin.reports') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard.admin.reports') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-chart-line-up text-base"></i>
                    <span>Reports</span>
                </a>

                <a href="{{ route('admin.providers.performance') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.providers.performance') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-chart-bar text-base"></i>
                    <span>Performance</span>
                </a>
            </div>
        </div>

        {{-- MANAGEMENT SECTION --}}
        <div class="border-b border-gray-800 pb-2">
            <button @click="managementOpen = !managementOpen" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-white transition">
                <span>Management</span>
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': managementOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="managementOpen" x-collapse class="mt-1 space-y-0.5">
                <a href="{{ route('admin.categories.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-folder-notch text-base"></i>
                    <span>Categories</span>
                </a>

                <a href="{{ route('admin.services.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.services.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-scissors text-base"></i>
                    <span>Services</span>
                </a>

                <a href="{{ route('admin.providers.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.providers.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-users-three text-base"></i>
                    <span>Providers</span>
                </a>

                <a href="{{ route('admin.team_members.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.team_members.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-users text-base"></i>
                    <span>Team Members</span>
                </a>

                <a href="{{ route('admin.slots.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.slots.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-clock text-base"></i>
                    <span>Time Slots</span>
                </a>

                <a href="{{ route('admin.messages') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.messages') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-envelope-simple text-base"></i>
                    <span>Messages</span>
                </a>

                <a href="{{ route('admin.webmail.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.webmail.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-envelope-open text-base"></i>
                    <span>Webmail</span>
                </a>

                <a href="{{ route('admin.gallery-instagram.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.gallery-instagram.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-instagram-logo text-base"></i>
                    <span>Gallery</span>
                </a>

                <a href="{{ route('admin.tags.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.tags.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-tag text-base"></i>
                    <span>Tags</span>
                </a>

                <a href="{{ route('admin.reviews.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.reviews.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-star text-base"></i>
                    <span>Reviews</span>
                </a>
            </div>
        </div>

        {{-- TOOLS SECTION --}}
        <div class="border-b border-gray-800 pb-2">
            <button @click="toolsOpen = !toolsOpen" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-white transition">
                <span>Tools</span>
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': toolsOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="toolsOpen" x-collapse class="mt-1 space-y-0.5">
                <a href="{{ route('admin.bookings.manual.create') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.bookings.manual.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-calendar-plus text-base"></i>
                    <span>Manual Booking</span>
                </a>

                <a href="{{ route('admin.services.pending') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.services.pending') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-hourglass text-base"></i>
                    <span>Pending Services</span>
                </a>

                <a href="{{ route('admin.emails.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.emails.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-paper-plane-tilt text-base"></i>
                    <span>Email Logs</span>
                </a>

                <a href="{{ route('admin.webhooks.index') }}" 
                   class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.webhooks.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="ph ph-webhook text-base"></i>
                    <span>Webhooks</span>
                </a>
            </div>
        </div>
    </div>

{{-- Provider Navigation --}}
@elseif($role === 'provider')
    <div class="space-y-1">
        <a href="{{ route('dashboard.provider') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard.provider') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-house text-base"></i>
            <span>Overview</span>
        </a>

        <a href="{{ route('provider.calendar') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('provider.calendar') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-calendar-check text-base"></i>
            <span>My Calendar</span>
        </a>

        <a href="{{ route('provider.services.index') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('provider.services.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-scissors text-base"></i>
            <span>My Services</span>
        </a>

        <a href="{{ route('provider.working-hours.edit') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('provider.working-hours.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-clock text-base"></i>
            <span>Working Hours</span>
        </a>
        
        <a href="{{ route('provider.reviews.index') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('provider.reviews.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-star text-base"></i>
            <span>My Reviews</span>
        </a>
    </div>

{{-- Customer Navigation --}}
@elseif($role === 'customer')
    <div class="space-y-1">
        <a href="{{ route('dashboard.customer') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard.customer') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-calendar-heart text-base"></i>
            <span>My Bookings</span>
        </a>

        <a href="{{ route('customer.reviews.index') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('customer.reviews.*') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-star text-base"></i>
            <span>My Reviews</span>
        </a>

        <a href="{{ route('customer.notifications') }}" 
           class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('customer.notifications') ? 'bg-rose-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <i class="ph ph-bell text-base"></i>
            <span>Notifications</span>
        </a>
    </div>
@endif

{{-- Account --}}
<div class="mt-6 pt-4 border-t border-gray-800">
    <a href="{{ route('profile.edit') }}" 
       class="group flex items-center gap-2 px-3 py-2 text-sm font-medium transition-colors text-gray-400 hover:bg-gray-800 hover:text-white">
        <i class="ph ph-gear text-base"></i>
        <span>Settings</span>
    </a>
</div>
