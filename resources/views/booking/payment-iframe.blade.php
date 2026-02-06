@extends('layouts.payment')

@section('title', 'Complete Your Payment')

@section('content')
<div class="iframe-container" id="iframeContainer">
    <iframe src="{{ $iframeUrl }}" 
            allow="payment *" 
            id="weflexIframe"></iframe>
</div>

{{-- Processing overlay (shown ONLY after user submits payment) --}}
<div id="processingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; align-items:center; justify-content:center; flex-direction:column;">
    <div style="text-align: center; color: white;">
        <div style="width: 60px; height: 60px; border: 4px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
        <h2 style="font-size: 24px; margin-bottom: 10px;">Processing Payment...</h2>
        <p style="font-size: 16px; color: rgba(255,255,255,0.8);">Please wait while we confirm your payment. This may take a few seconds.</p>
        <p id="statusMessage" style="font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 20px;">Waiting for confirmation...</p>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@push('scripts')
<script>
let pollingInterval = null;
let pollCount = 0;
let paymentSubmitted = false; // Track if payment form was submitted
const MAX_POLLS = 60; // 60 polls * 2 seconds = 2 minutes max
const bookingReference = '{{ session("last_booking_reference") ?? "" }}';

console.log('Payment page loaded. Booking reference:', bookingReference);

// Listen for payment events from WeFlexfy iframe
window.addEventListener("message", function (event) {
    console.log('Received message from iframe:', event.data);
    
    // Only process messages from WeFlexfy
    if (event.data?.type === 'PAYMENT_STATUS') {
        handlePaymentEvent(event.data);
    }
});

function handlePaymentEvent(data) {
    console.log('Payment status:', data.status);
    
    switch (data.status) {
        case 'init':
            // Iframe loaded - do nothing, let user interact
            console.log('Payment iframe initialized and ready');
            break;
            
        case 'success':
            // ⚠️ CRITICAL: This means payment FORM was submitted
            // NOT that payment was successful!
            // Show overlay and start polling for webhook confirmation
            if (!paymentSubmitted) {
                paymentSubmitted = true;
                console.log('Payment form submitted. Starting verification...');
                showProcessingOverlay();
                startPolling();
            }
            break;
            
        case 'failed':
            // Client-side failure (validation error, network issue, etc.)
            console.log('Payment form submission failed - redirecting...');
            // Redirect immediately without alert (iframe already shows error)
            setTimeout(() => {
                window.location.href = "{{ route('booking.step1') }}?error=payment_failed";
            }, 1000); // 1 second delay to let user see the error
            break;
            
        case 'close':
        case 'cancelled':
            // User cancelled/closed payment
            console.log('Payment cancelled by user - redirecting...');
            setTimeout(() => {
                window.location.href = "{{ route('booking.step1') }}?info=payment_cancelled";
            }, 500);
            break;
    }
}

function showProcessingOverlay() {
    const overlay = document.getElementById('processingOverlay');
    const container = document.getElementById('iframeContainer');
    
    if (overlay && container) {
        overlay.style.display = 'flex';
        container.style.display = 'none';
        console.log('Processing overlay shown');
    }
}

function updateStatus(message) {
    const statusEl = document.getElementById('statusMessage');
    if (statusEl) {
        statusEl.textContent = message;
    }
}

function startPolling() {
    if (!bookingReference) {
        console.error('No booking reference found for polling');
        updateStatus('Error: Missing booking reference');
        setTimeout(() => {
            window.location.href = "{{ route('booking.step1') }}?error=missing_reference";
        }, 2000);
        return;
    }

    console.log('Starting polling for booking:', bookingReference);
    updateStatus('Checking payment status...');
    
    pollingInterval = setInterval(() => {
        pollCount++;
        
        fetch(`{{ url('/booking/payment-status') }}/${bookingReference}`)
            .then(response => response.json())
            .then(data => {
                console.log(`Poll ${pollCount}:`, data);
                
                if (data.status === 'success') {
                    // ✅ Payment confirmed by webhook!
                    clearInterval(pollingInterval);
                    updateStatus('Payment confirmed! Redirecting...');
                    setTimeout(() => {
                        window.location.href = "{{ route('booking.success') }}";
                    }, 1000);
                    
                } else if (data.status === 'failed') {
                    // ❌ Payment failed (confirmed by webhook)
                    clearInterval(pollingInterval);
                    updateStatus('Payment failed. Redirecting...');
                    setTimeout(() => {
                        window.location.href = "{{ route('booking.step1') }}?error=payment_declined";
                    }, 2000);
                    
                } else if (data.status === 'pending') {
                    // ⏳ Still waiting for webhook
                    updateStatus(`Waiting for confirmation... (${pollCount * 2}s)`);
                    
                    // Timeout after 2 minutes
                    if (pollCount >= MAX_POLLS) {
                        clearInterval(pollingInterval);
                        updateStatus('Payment verification is taking longer than expected.');
                        setTimeout(() => {
                            window.location.href = "{{ route('booking.step1') }}?warning=payment_timeout";
                        }, 3000);
                    }
                } else {
                    // Unknown status
                    updateStatus(data.message || 'Checking...');
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
                pollCount++;
                
                if (pollCount >= MAX_POLLS) {
                    clearInterval(pollingInterval);
                    updateStatus('Unable to verify payment. Please contact support.');
                }
            });
            
    }, 2000); // Poll every 2 seconds
}

// Safety check: If overlay is visible on page load, hide it
window.addEventListener('load', function() {
    const overlay = document.getElementById('processingOverlay');
    if (overlay && overlay.style.display !== 'none') {
        console.warn('Overlay was visible on load - hiding it');
        overlay.style.display = 'none';
    }
});
</script>
@endpush
