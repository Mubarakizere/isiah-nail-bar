@extends('layouts.public')

@section('title', 'Frequently Asked Questions')

@section('content')
<x-page-header title="Frequently Asked Questions" subtitle="Find answers to common questions about booking, payments, policies, and more." />

{{-- 🎁 First-Time Offer Highlight --}}
<div class="container my-5">
    <div class="alert alert-warning shadow-sm text-center fs-5 rounded" data-aos="zoom-in">
        🎉 <strong>First-Time Clients</strong> get <strong>10% OFF</strong> your first appointment! Book now and glow with Isaiah Nail Bar.
    </div>
</div>

{{-- 📘 FAQ Accordion Section --}}
<div class="container pb-5" data-aos="fade-up">
    <div class="accordion shadow-sm" id="faqAccordion">
        {{-- Booking Requirements --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
            <h2 class="accordion-header" id="headingBooking">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooking">
                    <i class="ph ph-calendar-check me-2"></i> Is a deposit required to book an appointment?
                </button>
            </h2>
            <div id="collapseBooking" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, a <strong>non-refundable deposit</strong> of <strong>40%</strong> is required to secure your booking. This amount will be deducted from your final payment.
                </div>
            </div>
        </div>

        {{-- Payment Options --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
            <h2 class="accordion-header" id="headingPayment">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment">
                    <i class="ph ph-credit-card me-2"></i> What are the available payment methods?
                </button>
            </h2>
            <div id="collapsePayment" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    We accept payments via <strong>MTN Mobile Money</strong>, <strong>Cash</strong>, or <strong>Bank Transfer</strong> depending on service type. Payment instructions will be provided after confirming the booking.
                </div>
            </div>
        </div>

        {{-- Late Arrival --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="150">
            <h2 class="accordion-header" id="headingLate">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLate">
                    <i class="ph ph-clock-afternoon me-2"></i> What happens if I’m late to my appointment?
                </button>
            </h2>
            <div id="collapseLate" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    If you are more than <strong>15 minutes late</strong>, your appointment may be canceled and your deposit forfeited. Please arrive on time to avoid disruptions.
                </div>
            </div>
        </div>

        {{-- Cancellation Policy --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
            <h2 class="accordion-header" id="headingCancel">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCancel">
                    <i class="ph ph-x-circle me-2"></i> Can I cancel or reschedule my booking?
                </button>
            </h2>
            <div id="collapseCancel" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, but cancellations must be made <strong>at least 48 hours</strong> before the appointment. Otherwise, the deposit will be forfeited. Rescheduling is allowed within the same notice period.
                </div>
            </div>
        </div>

        {{-- Guest Policy --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="250">
            <h2 class="accordion-header" id="headingGuests">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuests">
                    <i class="ph ph-users-three me-2"></i> Can I bring someone with me to my appointment?
                </button>
            </h2>
            <div id="collapseGuests" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    At this time, <strong>no extra guests</strong> are allowed in the salon due to space and hygiene policies. Please come alone to your appointment.
                </div>
            </div>
        </div>

        {{-- Silent Appointment --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
            <h2 class="accordion-header" id="headingSilent">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSilent">
                    <i class="ph ph-speaker-slash me-2"></i> What is a Silent Appointment?
                </button>
            </h2>
            <div id="collapseSilent" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    If you're feeling tired, anxious, or simply prefer a quiet session, you may request a <strong>Silent Appointment</strong>. We respect your comfort—no explanation needed.
                </div>
            </div>
        </div>

        {{-- Pricing Concerns --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="350">
            <h2 class="accordion-header" id="headingPricing">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePricing">
                    <i class="ph ph-tag me-2"></i> Why are your prices fixed?
                </button>
            </h2>
            <div id="collapsePricing" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    We maintain a standard of excellence and only use premium, safe products. Pricing reflects quality and time. We do not negotiate pricing, but we're happy to work within your needs for a custom set.
                </div>
            </div>
        </div>

        {{-- Ambassador Program --}}
        <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
            <h2 class="accordion-header" id="headingAmbassador">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAmbassador">
                    <i class="ph ph-hand-heart me-2"></i> How do I become a Nail Ambassador?
                </button>
            </h2>
            <div id="collapseAmbassador" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Nail Ambassadors help promote Isaiah Nail Bar through social content and referrals. To qualify, you must:
                    <ul class="mt-2">
                        <li>Follow us on Instagram (@isaiahnairs)</li>
                        <li>Have at least <strong>10000 followers</strong></li>
                        <li>Like & engage with all our posts</li>
                        <li>Create unique and creative nail sets</li>
                        <li>Come in for a <strong>new set every 2 weeks</strong></li>
                    </ul>
                    <p class="mt-2 mb-0">Ambassadors receive special perks including:</p>
                    <ul>
                        <li>Discounted full sets (e.g., $45 for any style & length)</li>
                        <li>Discounted fills (e.g., $30)</li>
                        <li>Birthday sets for only $20</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
