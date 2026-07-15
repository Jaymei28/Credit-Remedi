<table id="disputesTable" class="table table-hover align-middle">
    <thead>
        <tr>
            <th style="width: 120px;">Date</th>
            <th>Subject</th>
            @if (auth()->user()->role === 'admin')
                <th style="width: 150px;">User</th>
            @endif
            <th style="width: 180px;">Creditor</th>
            <th style="width: 150px;">Status</th>
            <th style="width: 120px;">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($disputes as $dispute)
            @php
                $status = $dispute->posted_1 ? 'approved' : 'pending';
                $statusLabel = $dispute->posted_1 ? 'Posted' : 'Pending';
                $statusIcon = $dispute->posted_1 ? '✅' : '⏳';
            @endphp
            <tr onclick="window.location='{{ route('disputes.show', $dispute->id) }}'" style="cursor: pointer;">
                <td data-label="Date" data-order="{{ $dispute->created_at->timestamp }}">
                    <small class="text-muted">{{ $dispute->created_at->format('M d, Y') }}</small>
                </td>
                <td data-label="Subject">
                    <strong>{{ $dispute->dispute_reason ?? \Illuminate\Support\Str::of($dispute->letter_content)->after('Subject:')->before("\n")->trim() }}</strong>
                </td>
                @if (auth()->user()->role === 'admin')
                    <td data-label="User">{{ $dispute->user->name ?? 'N/A' }}</td>
                @endif
                <td data-label="Creditor">{{ $dispute->creditor_name ?? 'N/A' }}</td>
                <td data-label="Status">
                    <span class="status-badge status-{{ $status }}">
                        {{ $statusIcon }} {{ $statusLabel }}
                    </span>
                </td>
                <td data-label="Action">
                    <a href="{{ route('disputes.show', $dispute->id) }}" class="btn btn-sm btn-outline-primary w-100" onclick="event.stopPropagation()">
                        View
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ auth()->user()->role === 'admin' ? '6' : '5' }}" class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mb-1 mt-3" style="font-size: 1.1rem; font-weight: 600;">No disputes found</p>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Create your first dispute letter to get started</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<style>
    /* Status Badges in Table */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: var(--border-radius-sm);
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .status-approved {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-denied {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
</style>
