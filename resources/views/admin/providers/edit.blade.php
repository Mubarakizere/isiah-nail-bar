@extends('layouts.dashboard')

@section('title', 'Edit Provider')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    {{-- Header Section --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-2">Edit Provider</h1>
            <p class="text-gray-500 text-lg">Update provider profile information</p>
        </div>
        <a href="{{ route('admin.providers.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 border border-gray-200 shadow-sm transition-all duration-300">
            <i class="ph ph-arrow-left mr-2"></i>Back to Providers
        </a>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-100 rounded-2xl p-5 mb-8 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph ph-warning-circle text-xl text-red-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Card --}}
    <form action="{{ route('admin.providers.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Full Name --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Provider Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $provider->name) }}" 
                               required
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Phone <span class="text-gray-400 text-xs font-medium ml-1">(Optional)</span>
                        </label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone', $provider->phone) }}"
                               placeholder="+1 (555) 000-0000"
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                    </div>

                    {{-- Email --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Email <span class="text-gray-400 text-xs font-medium ml-1">(Optional)</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $provider->email) }}"
                               placeholder="provider@isaiahnailbar.com"
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                        <p class="text-xs text-gray-500 mt-2 flex items-center">
                            <i class="ph ph-envelope-simple mr-1.5"></i>
                            If empty, notifications default to general emails (info@isaiahnailbar.com).
                        </p>
                    </div>

                    {{-- Photo --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Provider Photo</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                            {{-- Current Photo Preview --}}
                            <div class="flex-shrink-0">
                                @if($provider->photo)
                                    <div class="relative w-24 h-24 rounded-2xl overflow-hidden shadow-sm border border-gray-100 group">
                                        <img src="{{ asset('storage/'.$provider->photo) }}" alt="Provider Photo" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                            <i class="ph ph-image text-white text-xl"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-sm border border-gray-100">
                                        {{ strtoupper(substr($provider->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Upload Area --}}
                            <div class="flex-1 w-full">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-2xl hover:border-indigo-500 hover:bg-indigo-50/50 transition-colors duration-300">
                                    <div class="space-y-2 text-center">
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="photo-upload" class="relative cursor-pointer bg-white rounded-md font-semibold text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span>Upload a new photo</span>
                                                <input id="photo-upload" name="photo" type="file" class="sr-only" accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 30MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" 
                                  rows="4"
                                  placeholder="Tell us about this provider..."
                                  class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-y">{{ old('bio', $provider->bio) }}</textarea>
                    </div>

                    {{-- Assign Services --}}
                    <div class="md:col-span-2 mt-4 pt-6 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
                            <div>
                                <label class="block text-lg font-bold text-gray-900">
                                    Assigned Services
                                </label>
                                <p class="text-sm text-gray-500">Update the services this provider is qualified to perform.</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.querySelectorAll('.service-checkbox').forEach(el => {el.checked = true; el.dispatchEvent(new Event('change'));})" class="text-xs px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg transition font-bold border border-gray-200 shadow-sm">Select All</button>
                                <button type="button" onclick="document.querySelectorAll('.service-checkbox').forEach(el => {el.checked = false; el.dispatchEvent(new Event('change'));})" class="text-xs px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg transition font-bold border border-gray-200 shadow-sm">Clear All</button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($services as $service)
                                <div class="relative flex items-start">
                                    <input type="checkbox" 
                                           name="services[]" 
                                           value="{{ $service->id }}"
                                           id="service-{{ $service->id }}" 
                                           {{ in_array($service->id, old('services', $provider->services->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="service-checkbox peer sr-only">
                                    <label for="service-{{ $service->id }}" 
                                           class="w-full p-4 bg-white border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/30 transition-all duration-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-gray-900 peer-checked:text-indigo-900">{{ $service->name }}</span>
                                            <div class="w-5 h-5 rounded border-2 flex items-center justify-center border-gray-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-colors">
                                                <i class="ph ph-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.providers.index') }}" 
                   class="w-full sm:w-auto px-6 py-3.5 bg-white text-gray-700 font-bold rounded-xl hover:bg-gray-50 border border-gray-200 shadow-sm transition-all duration-300 text-center">
                    Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-gray-900 to-gray-800 text-white font-bold rounded-xl hover:from-black hover:to-gray-900 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                    <i class="ph ph-check-circle mr-2 text-xl"></i>Update Provider
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Simple script to show selected file name
    document.getElementById('photo-upload').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const dropzone = this.closest('.border-dashed');
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50/50');
            const p = dropzone.querySelector('p.text-xs');
            p.innerHTML = `<span class="font-bold text-indigo-700">Selected: ${fileName}</span>`;
            p.classList.remove('text-gray-500');
        }
    });

    // Make the custom checkboxes work visually
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = document.querySelector(`label[for="${this.id}"]`);
            const icon = label.querySelector('.ph-check');
            const iconContainer = label.querySelector('.w-5.h-5');
            
            if(this.checked) {
                label.classList.add('border-indigo-500', 'bg-indigo-50/30');
                label.classList.remove('border-gray-100');
                iconContainer.classList.add('border-indigo-500', 'bg-indigo-500');
                iconContainer.classList.remove('border-gray-300');
                icon.classList.remove('opacity-0');
            } else {
                label.classList.remove('border-indigo-500', 'bg-indigo-50/30');
                label.classList.add('border-gray-100');
                iconContainer.classList.remove('border-indigo-500', 'bg-indigo-500');
                iconContainer.classList.add('border-gray-300');
                icon.classList.add('opacity-0');
            }
        });
        
        // Trigger initially
        checkbox.dispatchEvent(new Event('change'));
    });
</script>
@endpush
@endsection
