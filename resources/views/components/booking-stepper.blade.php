@props(['current' => 1])

@php
    $steps = [
        1 => [
            'label' => 'Services',
            'icon' => 'ph-scissors',
            'description' => 'Choose your services'
        ],
        2 => [
            'label' => 'Provider',
            'icon' => 'ph-user',
            'description' => 'Select your stylist'
        ],
        3 => [
            'label' => 'Date & Time',
            'icon' => 'ph-calendar',
            'description' => 'Pick your slot'
        ],
        4 => [
            'label' => 'Payment',
            'icon' => 'ph-credit-card',
            'description' => 'Payment details'
        ],
        5 => [
            'label' => 'Confirm',
            'icon' => 'ph-check-circle',
            'description' => 'Review & confirm'
        ]
    ];
    
    $totalSteps = count($steps);
@endphp

<div class="booking-stepper-container">
    <div class="container py-4 py-md-5">
        {{-- Mobile Stepper (Compact) --}}
        <div class="d-md-none">
            <div class="step-progress-mobile mb-4">
                <div class="progress-text text-center mb-3">
                    <span class="text-display fw-bold h4" style="color: var(--primary);">Step {{ $current }} of {{ $totalSteps }}</span>
                    <p class="text-muted small mb-0">{{ $steps[$current]['description'] }}</p>
                </div>
                
                <div class="progress" style="height: 8px; background: var(--gray-200); border-radius: var(--radius-full);">
                    <div class="progress-bar bg-gradient-primary" 
                         role="progressbar" 
                         style="width: {{ (($current - 1) / ($totalSteps - 1)) * 100 }}%; transition: width 0.5s var(--ease-out);"
                         aria-valuenow="{{ $current }}"
                         aria-valuemin="1" 
                         aria-valuemax="{{ $totalSteps }}">
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Desktop Stepper (Full Featured) --}}
        <div class="d-none d-md-block">
            <div class="steps-wrapper">
                @foreach($steps as $step => $data)
                    @php
                        $isCompleted = $step < $current;
                        $isCurrent = $step == $current;
                        $isFuture = $step > $current;
                    @endphp
                    
                    {{-- Step Item --}}
                    <div class="step-item {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isFuture ? 'future' : '' }}">
                        {{-- Circle with Number/Check --}}
                        <div class="step-circle">
                            @if($isCompleted)
                                <i class="ph ph-check step-icon"></i>
                            @else
                                <span class="step-number">{{ $step }}</span>
                            @endif
                            
                            {{-- Animated Ring for Current Step --}}
                            @if($isCurrent)
                                <svg class="step-ring" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="48" />
                                </svg>
                            @endif
                        </div>
                        
                        {{-- Step Label --}}
                        <div class="step-label">
                            <div class="step-title">{{ $data['label'] }}</div>
                            <div class="step-description">{{ $data['description'] }}</div>
                        </div>
                        
                        {{-- Connector Line (except for last step) --}}
                        @if($step < $totalSteps)
                            <div class="step-connector {{ $step < $current ? 'completed' : '' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
/* ========================================
   MODERN BOOKING STEPPER STYLES
   ======================================== */

.booking-stepper-container {
    background: linear-gradient(180deg, var(--gray-50) 0%, var(--white) 100%);
    border-bottom: 1px solid var(--gray-200);
}

/* === DESKTOP STEPPER === */
.steps-wrapper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    max-width: 900px;
    margin: 0 auto;
}

.step-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

/* Step Circle */
.step-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-bold);
    font-size: var(--text-lg);
    position: relative;
    transition: all var(--transition-smooth);
    margin-bottom: var(--space-3);
}

/* Future Step (Gray, Small) */
.step-item.future .step-circle {
    background: var(--white);
    border: 2px solid var(--gray-300);
    color: var(--gray-500);
    width: 48px;
    height: 48px;
    font-size: var(--text-base);
}

/* Current Step (Large, Primary, Animated) */
.step-item.current .step-circle {
    background: var(--gradient-primary);
    border: none;
    color: var(--white);
    width: 64px;
    height: 64px;
    font-size: var(--text-xl);
    box-shadow: var(--shadow-primary);
    animation: pulseStep 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Completed Step (Success Green with Check) */
.step-item.completed .step-circle {
    background: var(--success);
    border: none;
    color: var(--white);
    width: 56px;
    height: 56px;
    box-shadow: var(--shadow-success);
}

.step-item.completed .step-icon {
    font-size: var(--text-2xl);
    animation: checkmarkPop 0.4s var(--ease-bounce);
}

/* Animated Ring Around Current Step */
.step-ring {
    position: absolute;
    width: 76px;
    height: 76px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: rotateRing 3s linear infinite;
}

.step-ring circle {
    fill: none;
    stroke: var(--primary-light);
    stroke-width: 2;
    stroke-dasharray: 150 150;
    stroke-linecap: round;
}

/* Step Labels */
.step-label {
    text-align: center;
    transition: all var(--transition-base);
}

.step-title {
    font-family: var(--font-accent);
    font-weight: var(--font-semibold);
    font-size: var(--text-sm);
    margin-bottom: var(--space-1);
    color: var(--gray-700);
    transition: all var(--transition-base);
}

.step-description {
    font-size: var(--text-xs);
    color: var(--gray-500);
    transition: all var(--transition-base);
}

/* Active Step Label Styling */
.step-item.current .step-title {
    color: var(--primary);
    font-size: var(--text-base);
    font-weight: var(--font-bold);
}

.step-item.current .step-description {
    color: var(--primary-dark);
}

/* Completed Step Label */
.step-item.completed .step-title {
    color: var(--success);
}

/* Connector Line Between Steps */
.step-connector {
    position: absolute;
    top: 28px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: var(--gray-300);
    z-index: -1;
    transition: background var(--transition-slow);
}

.step-connector.completed {
    background: var(--success);
    animation: fillLine 0.5s var(--ease-out) forwards;
}

/* === ANIMATIONS === */

@keyframes pulseStep {
    0%, 100% {
        transform: scale(1);
        box-shadow: var(--shadow-primary);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 12px 32px rgba(233, 30, 99, 0.35);
    }
}

@keyframes rotateRing {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes checkmarkPop {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes fillLine {
    from { width: 0; }
    to { width: 100%; }
}

/* === MOBILE STEPPER === */
.step-progress-mobile {
    padding: 0 var(--space-4);
}

.progress-text {
    animation: slideDown 0.3s var(--ease-out);
}

/* === RESPONSIVE === */

@media (max-width: 992px) {
    .steps-wrapper {
        max-width: 700px;
    }
    
    .step-description {
        display: none;
    }
}

@media (max-width: 768px) {
    .step-circle {
        width: 44px !important;
        height: 44px !important;
        font-size: var(--text-sm) !important;
    }
    
    .step-item.current .step-circle {
        width: 52px !important;
        height: 52px !important;
    }
    
    .step-ring {
        width: 64px;
        height: 64px;
    }
    
    .step-title {
        font-size: 0.7rem;
    }
    
    .step-connector {
        top: 22px;
    }
}

@media (max-width: 576px) {
    .booking-stepper-container {
        padding-top: var(--space-2);
        padding-bottom: var(--space-2);
    }
}

/* === ACCESSIBILITY === */

@media (prefers-reduced-motion: reduce) {
    .step-circle,
    .step-label,
    .step-connector,
    .progress-bar {
        transition: none !important;
        animation: none !important;
    }
}

/* === PRINT STYLES === */

@media print {
    .booking-stepper-container {
        background: white !important;
        border-bottom: none !important;
    }
    
    .step-ring,
    .progress-bar {
        display: none !important;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
// Add smooth scroll behavior when returning to booking flow
document.addEventListener('DOMContentLoaded', function() {
    const stepper = document.querySelector('.booking-stepper-container');
    if (stepper && window.location.hash === '#stepper') {
        stepper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
@endPushOnce
