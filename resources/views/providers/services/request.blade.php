@extends('layouts.dashboard')

@section('title', 'Request a New Service')

@section('content')
<div class="container my-4">
    <h2 class="fw-bold text-center mb-4">➕ Request New Service</h2>

    <form method="POST" action="{{ route('provider.services.store') }}" enctype="multipart/form-data" class="card shadow-sm p-4">
        @csrf

        <div class="mb-3">
            <label class="form-label">Service Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Duration (minutes)</label>
            <input type="number" name="duration_minutes" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (RWF)</label>
            <input type="number" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
                <option value="">Select category</option>
                <option value="Manicure">Manicure</option>
                <option value="Pedicure">Pedicure</option>
                <option value="Acrylic Nails">Acrylic Nails</option>
                <option value="Gel Nails">Gel Nails</option>
                <option value="Nail Art">Nail Art</option>
                <option value="French Tips">French Tips</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>
@endsection
