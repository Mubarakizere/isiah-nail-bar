@extends('layouts.dashboard')

@section('title', 'Webhook Logs')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-bell-ring me-1 text-primary"></i> Webhook Logs
    </h2>

    @if($logs->count())
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Payload</th>
                        <th>Received At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $log->type) }}</td>
                        <td>
                            @if ($log->status === 'success' || $log->status === 'SUCCESS')
                                <span class="badge bg-success">Success</span>
                            @elseif ($log->status === 'failed' || $log->status === 'FAILED')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($log->status ?? 'N/A') }}</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#payload-{{ $log->id }}" aria-expanded="false" aria-controls="payload-{{ $log->id }}">
                                View Payload
                            </button>
                            <div class="collapse mt-2" id="payload-{{ $log->id }}" style="max-height: 400px; overflow:auto; white-space: pre-wrap; font-family: monospace;">
                                {{ json_encode($log->payload, JSON_PRETTY_PRINT) }}
                            </div>
                        </td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 d-flex justify-content-center">
                {{ $logs->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-info text-center">
            No webhook logs found.
        </div>
    @endif
</div>
@endsection
