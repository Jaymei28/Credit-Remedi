@extends('layouts.app')

@section('title', 'Waitlist Report')

@section('content')

<style>
    @media (min-width: 1200px) {
        .container {
            max-width: 1140px;
        }
    }

    /* Pagination Styling - Page numbers only */
    .pagination {
        gap: 0.5rem;
    }

    .pagination .page-item .page-link,
    .pagination .page-link {
        background-color: #1a202c !important;
        color: #ffffff !important;
        border-color: #4a5568 !important;
        padding: 0.5rem 1rem !important;
        border-radius: 0.375rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
    }

    .pagination .page-item .page-link:hover,
    .pagination .page-link:hover {
        background-color: #2d3748 !important;
        color: #ffffff !important;
        border-color: #667eea !important;
        transform: translateY(-1px);
    }

    .pagination .page-item.active .page-link {
        background-color: #667eea !important;
        border-color: #667eea !important;
        color: #ffffff !important;
    }

    .pagination .page-item.disabled .page-link {
        background-color: #1a202c !important;
        color: #718096 !important;
        border-color: #4a5568 !important;
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Hide Previous and Next buttons */
    .pagination .page-item:first-child,
    .pagination .page-item:last-child {
        display: none !important;
    }

</style>

<div class="container" style="margin-top: 80px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Waitlist Report</h2>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <table id="waitlistTable" class="table table-dark table-bordered table-hover align-middle">
        <thead style="background-color: #2a3f5f;">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Challenge</th>
                <th>Usage</th>
                <th>Timeline</th>
                <th>Referrer</th>
                <th>Referrals</th>
                <th>Qualified</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($waitlistUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->challenge }}</td>
                    <td>{{ $user->usage }}</td>
                    <td>{{ $user->timeline }}</td>
                    <td>
                        @if($user->referrer)
                            {{ $user->referrer->name }}<br>
                            <small class="text-muted">{{ $user->referrer->email }}</small>
                        @else
                            <span class="text-muted">–</span>
                        @endif
                    </td>
                    <td>{{ $user->referral_count }}</td>
                    <td>
                        @if($user->is_qualified)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">No waitlist users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 mb-5 d-flex justify-content-center">
        {{ $waitlistUsers->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pagination buttons are hidden via CSS
    });
</script>
@endpush

