@extends('layouts.public')

@section('title', 'Step 1: Choose Services')

@section('content')
@include('partials.booking-progress', ['currentStep' => 1])

<div class="container py-5">
    <div class="row">
        <!-- Filter & Summary -->
        <div class="col-md-4 mb-4" data-aos="fade-right">
            <div class="mb-4">
                <h2 class="fw-bold">Choose Services</h2>
                <p class="text-muted">Select one or more services to proceed. Click "Preview" for more info.</p>
            </div>

            <label class="form-label">Jump to Category:</label>
            <select id="categoryFilter" class="form-select mb-4">
                <option value="">— Select —</option>
                @foreach ($categories as $category)
                    <option value="cat-{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <div class="border rounded p-3 shadow-sm bg-light sticky-top" style="top: 90px;">
                <h6 class="fw-bold mb-3">Selected Services</h6>
                <ul id="summaryList" class="list-unstyled small text-muted mb-2"></ul>
                <p class="fw-semibold">Total: <span id="summaryTotal" class="text-primary">RWF 0</span></p>

                <button type="submit" form="servicesForm" class="btn btn-primary w-100 d-none" id="nextButton">
                    <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner" role="status" aria-hidden="true"></span>
                    Continue
                </button>
            </div>
        </div>

        <!-- Services -->
        <div class="col-md-8" data-aos="fade-left">
            <form method="POST" action="{{ route('booking.step1.submit') }}" id="servicesForm">
                @csrf
                <div class="accordion" id="servicesAccordion">
                    @foreach ($categories as $category)
                        @if($category->services->isNotEmpty())
                            <div class="accordion-item mb-3" id="cat-{{ $category->id }}">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}">
                                        {{ $category->name }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $category->id }}" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            @foreach ($category->services as $service)
                                                <div class="col-sm-6 col-md-4">
                                                    <label class="card service-card d-block">
                                                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" class="d-none"
                                                            data-name="{{ $service->name }}"
                                                            data-price="{{ $service->price }}"
                                                            data-description="{{ $service->description }}"
                                                            data-image="{{ $service->image ? asset('storage/' . $service->image) : asset('images/default-service.jpg') }}"
                                                            {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}>

                                                        <img src="{{ $service->image ? asset('storage/' . $service->image) : asset('images/default-service.jpg') }}"
                                                            alt="{{ $service->name }}" class="card-img-top service-img">

                                                        <div class="card-body text-center px-2">
                                                            <h6 class="fw-bold mb-1 text-dark">{{ $service->name }}</h6>
                                                            <p class="text-muted small mb-2">{{ Str::limit($service->description, 50) }}</p>
                                                            <p class="fw-semibold text-primary mb-0">RWF {{ number_format($service->price) }}</p>
                                                        </div>

                                                        <span class="checkmark badge bg-success position-absolute top-0 end-0 m-2 d-none">✓</span>

                                                        <button type="button" class="btn btn-sm btn-link position-absolute bottom-0 end-0 m-2 text-primary preview-service" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                            Preview
                                                        </button>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Summary -->
<div class="d-md-none fixed-bottom bg-white border-top p-3 shadow-lg">
    <div class="d-flex justify-content-between align-items-center">
        <strong>Total: <span id="summaryTotalMobile">RWF 0</span></strong>
        <button type="submit" form="servicesForm" class="btn btn-primary" id="mobileNextBtn">Continue</button>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="previewLabel">Service Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <img id="previewImage" src="" alt="" class="img-fluid rounded mb-3 w-100" style="max-height: 300px; object-fit: cover;">
        <p class="fw-bold h5 mb-2" id="previewName"></p>
        <p class="text-muted" id="previewDescription"></p>
        <p class="fw-semibold text-primary">Price: <span id="previewPrice"></span> RWF</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.service-card');
        const nextBtn = document.getElementById('nextButton');
        const mobileNextBtn = document.getElementById('mobileNextBtn');
        const summaryList = document.getElementById('summaryList');
        const summaryTotal = document.getElementById('summaryTotal');
        const summaryTotalMobile = document.getElementById('summaryTotalMobile');

        function updateSummary() {
            let total = 0;
            summaryList.innerHTML = '';

            document.querySelectorAll('input[type="checkbox"][name="service_ids[]"]').forEach(cb => {
                const card = cb.closest('.service-card');
                const checkmark = card.querySelector('.checkmark');

                if (cb.checked) {
                    total += parseFloat(cb.dataset.price);
                    card.classList.add('selected');
                    checkmark.classList.remove('d-none');
                    summaryList.innerHTML += `<li><i class="bi bi-check-circle text-success me-1"></i> ${cb.dataset.name}</li>`;
                } else {
                    card.classList.remove('selected');
                    checkmark.classList.add('d-none');
                }
            });

            summaryTotal.textContent = 'RWF ' + total.toLocaleString();
            summaryTotalMobile.textContent = summaryTotal.textContent;
            nextBtn.classList.toggle('d-none', total === 0);
        }

        cards.forEach(card => {
            card.addEventListener('click', e => {
                if (!e.target.classList.contains('preview-service')) {
                    const cb = card.querySelector('input[type="checkbox"]');
                    cb.checked = !cb.checked;
                    updateSummary();
                }
            });
        });

        document.querySelectorAll('.preview-service').forEach(btn => {
            btn.addEventListener('click', function () {
                const cb = this.closest('.service-card').querySelector('input[type="checkbox"]');
                document.getElementById('previewName').textContent = cb.dataset.name;
                document.getElementById('previewDescription').textContent = cb.dataset.description;
                document.getElementById('previewPrice').textContent = parseFloat(cb.dataset.price).toLocaleString();
                document.getElementById('previewImage').src = cb.dataset.image;
            });
        });

        document.getElementById('categoryFilter').addEventListener('change', function () {
            if (this.value) {
                const section = document.getElementById(this.value);
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    const header = section.querySelector('.accordion-button');
                    if (header && !header.classList.contains('collapsed')) return;
                    header?.click();
                }
            }
        });

        // Spinner on submit
        nextBtn?.addEventListener('click', function () {
            document.getElementById('submitSpinner').classList.remove('d-none');
        });

        mobileNextBtn?.addEventListener('click', function () {
            document.getElementById('submitSpinner').classList.remove('d-none');
        });

        updateSummary();
    });
</script>
@endpush

@push('styles')
<style>
    .service-img {
        height: 140px;
        object-fit: cover;
        transition: transform 0.2s ease-in-out;
        border-radius: 6px 6px 0 0;
    }

    .service-card {
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        position: relative;
        padding-bottom: 32px;
    }

    .service-card:hover .service-img {
        transform: scale(1.05);
    }

    .service-card.selected {
        border-color: #0d6efd;
        background-color: #eaf4ff;
        box-shadow: 0 0 0 2px #cfe2ff;
    }

    .checkmark {
        font-size: 0.8rem;
        padding: 4px 8px;
        border-radius: 12px;
        animation: bounceIn 0.3s ease;
    }

    @keyframes bounceIn {
        0% { transform: scale(0.5); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .preview-service {
        font-size: 0.8rem;
        background: rgba(255, 255, 255, 0.85);
        border-radius: 6px;
        padding: 2px 6px;
    }

    @media (max-width: 576px) {
        .sticky-top { position: static !important; }
        .service-img { height: 110px; }
    }
</style>
@endpush
