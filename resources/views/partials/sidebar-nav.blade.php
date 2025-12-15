@php
    $role = auth()->user()->getRoleNames()->first();
@endphp

{{-- Admin Navigation --}}
@if($role === 'admin')
    <div class="space-y-6">
        {{-- Dashboard Section --}}
        <div>
            <div class="px-2 mb-2">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Overview</h3>
            </div>
            <div class="space-y-1">
                <a href="{{ route('dashboard.admin') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard.admin') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('dashboard.admin') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-house text-lg"></i>
                    </div>
                    <span>Overview</span>
                </a>

                <a href="{{ route('dashboard.admin.bookings') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard.admin.bookings') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('dashboard.admin.bookings') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-calendar text-lg"></i>
                    </div>
                    <span>Bookings</span>
                </a>

                <a href="{{ route('dashboard.admin.reports') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard.admin.reports') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('dashboard.admin.reports') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-chart-line-up text-lg"></i>
                    </div>
                    <span>Reports</span>
                </a>

                 <a href="{{ route('admin.providers.performance') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.providers.performance') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.providers.performance') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-chart-bar text-lg"></i>
                    </div>
                    <span>Performance</span>
                </a>
            </div>
        </div>

        {{-- Management Section --}}
        <div>
            <div class="px-2 mb-2">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Management</h3>
            </div>
            <div class="space-y-1">
                <a href="{{ route('admin.categories.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-folder-notch text-lg"></i>
                    </div>
                    <span>Categories</span>
                </a>

                <a href="{{ route('admin.services.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.services.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.services.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-scissors text-lg"></i>
                    </div>
                    <span>Services</span>
                </a>

                <a href="{{ route('admin.providers.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.providers.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.providers.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-users-three text-lg"></i>
                    </div>
                    <span>Providers</span>
                </a>

                <a href="{{ route('admin.team_members.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.team_members.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.team_members.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-users text-lg"></i>
                    </div>
                    <span>Team Members</span>
                </a>

                <a href="{{ route('admin.slots.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.slots.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.slots.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-clock text-lg"></i>
                    </div>
                    <span>Time Slots</span>
                </a>

                <a href="{{ route('admin.messages') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.messages') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.messages') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-envelope-simple text-lg"></i>
                    </div>
                    <span>Messages</span>
                </a>

                <a href="{{ route('admin.webmail.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.webmail.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.webmail.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-envelope-open text-lg"></i>
                    </div>
                    <span>Webmail</span>
                </a>

                 <a href="{{ route('admin.gallery-instagram.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.gallery-instagram.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.gallery-instagram.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-instagram-logo text-lg"></i>
                    </div>
                    <span>Gallery</span>
                </a>
            </div>
        </div>
    </div>

{{-- Provider Navigation --}}
@elseif($role === 'provider')
    <div class="space-y-6">
        <div>
            <div class="px-2 mb-2">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Artist Studio</h3>
            </div>
            <div class="space-y-1">
                <a href="{{ route('dashboard.provider') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard.provider') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('dashboard.provider') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-house text-lg"></i>
                    </div>
                    <span>Overview</span>
                </a>

                <a href="{{ route('provider.calendar') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('provider.calendar') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('provider.calendar') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-calendar-check text-lg"></i>
                    </div>
                    <span>My Calendar</span>
                </a>

                <a href="{{ route('provider.services.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('provider.services.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('provider.services.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-scissors text-lg"></i>
                    </div>
                    <span>My Services</span>
                </a>

                <a href="{{ route('provider.working-hours.edit') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('provider.working-hours.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('provider.working-hours.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-clock text-lg"></i>
                    </div>
                    <span>Working Hours</span>
                </a>
                
                 <a href="{{ route('provider.reviews.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('provider.reviews.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('provider.reviews.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-star text-lg"></i>
                    </div>
                    <span>My Reviews</span>
                </a>
            </div>
        </div>
    </div>

{{-- Customer Navigation --}}
@elseif($role === 'customer')
    <div class="space-y-6">
        <div>
            <div class="px-2 mb-2">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">My Experience</h3>
            </div>
            <div class="space-y-1">
                <a href="{{ route('dashboard.customer') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard.customer') ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('dashboard.customer') ? 'bg-white/20 text-white' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-calendar-heart text-lg"></i>
                    </div>
                    <span>My Bookings</span>
                </a>

                <a href="{{ route('customer.reviews.index') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('customer.reviews.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('customer.reviews.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-star text-lg"></i>
                    </div>
                    <span>My Reviews</span>
                </a>

                <a href="{{ route('customer.notifications') }}" 
                   class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('customer.notifications') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('customer.notifications') ? 'bg-rose-500/20 text-rose-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white' }}">
                        <i class="ph ph-bell text-lg"></i>
                    </div>
                    <span>Notifications</span>
                </a>
            </div>
        </div>
    </div>
@endif

{{-- Account --}}
<div class="mt-8 pt-6 border-t border-gray-800">
    <a href="{{ route('profile.edit') }}" 
       class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all text-gray-400 hover:bg-gray-800 hover:text-white">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-800 text-gray-400 group-hover:bg-gray-700 group-hover:text-white">
            <i class="ph ph-gear text-lg"></i>
        </div>
        <span>Settings</span>
    </a>
</div>
