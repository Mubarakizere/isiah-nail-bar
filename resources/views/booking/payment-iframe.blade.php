@extends('layouts.payment')

@section('title', 'Complete Your Payment')

@section('content')
<div class="iframe-container">
    <iframe src="{{ $iframeUrl }}" allow="payment *" id="weflexIframe"></iframe>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener("message", function (event) {
        if (event.data?.type === 'PAYMENT_STATUS') {
            switch (event.data.status) {
                case 'success':
                    window.location.href = "{{ route('booking.success') }}";
                    break;
                case 'failed':
                    window.location.href = "{{ route('booking.step1') }}";
                    break;
                case 'close':
                    window.location.href = "{{ route('booking.step1') }}";
                    break;
                default:
                    break;
            }
        }
    });
</script>
@endpush
