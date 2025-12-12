@extends('layouts.dashboard')

@section('title', 'Edit Profile')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Profile Settings</h1>
        <p class="text-gray-600">Manage your account information and security settings</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="ph ph-check-circle text-2xl text-green-600"></i>
            <p class="text-green-900 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning-circle text-2xl text-red-600 flex-shrink-0"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-800 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-3xl space-y-6">
        {{-- Profile Information Card --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-user-circle text-blue-600"></i>
                    Account Information
                </h2>
                <p class="text-sm text-gray-600 mt-1">Update your name and email address</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', auth()->user()->name) }}" 
                               required
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', auth()->user()->email) }}" 
                               required
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ url()->previous() }}" 
                       class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                        <i class="ph ph-arrow-left mr-2"></i>Back
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        <i class="ph ph-floppy-disk mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password Card --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ph ph-lock-key text-yellow-600"></i>
                    Change Password
                </h2>
                <p class="text-sm text-gray-600 mt-1">Update your password to keep your account secure</p>
            </div>

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PATCH')

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                        <input type="password" 
                               name="current_password" 
                               required
                               placeholder="Enter current password"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="Enter new password (min. 8 characters)"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" 
                               name="password_confirmation" 
                               required
                               placeholder="Re-enter new password"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition">
                        <i class="ph ph-shield-check mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
