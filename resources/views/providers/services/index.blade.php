@extends('layouts.dashboard')

@section('title', 'My Services')

@section('content')
<div class="container my-4">
    <h2 class="fw-bold mb-4 text-center">💅 My Services</h2>

    @if ($services->isEmpty())
        <p class="text-muted text-center">You haven’t added any services yet.</p>
    @else
        <div class="row">
            @foreach ($services as $service)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ asset('storage/' . ($service->image ?? 'default.jpg')) }}" class="card-img-top" alt="{{ $service->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $service->name }}</h5>
                            <p class="card-text text-muted">RWF {{ number_format($service->price) }}</p>
                            <p class="card-text">{{ Str::limit($service->description, 80) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $services->links() }}
    @endif
</div>
@endsection
