@extends('layouts.app')

@section('title', 'User Management')

@section('content')

<style>
    @media (min-width: 1200px) {
        .container {
            max-width: 1140px;
        }
    }

    /* Pagination Styling - Clean page numbers only */
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

    /* Hide Previous and Next buttons completely */
    .pagination .page-item:first-child,
    .pagination .page-item:last-child {
        display: none !important;
    }

</style>
<div class="container" style="margin-top: 80px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            + Add User
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <table id="usersTable" class="table table-dark table-bordered table-hover align-middle">
        <thead style="background-color: #2a3f5f;">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Plan Type</th>
                <th>Status</th>
                <th>Registered</th>
                <th style="width: 130px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>{{ $user->getPlanTypeLabel() }}</td>
                    <td>
                        @if($user->registration_status === 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($user->registration_status === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($user->registration_status === 'failed')
                            <span class="badge bg-danger" title="{{ $user->registration_error }}">Failed</span>
                        @else
                            <span class="badge bg-secondary">Unknown</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-warning" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-delete" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" data-name="{{ $user->name }}">
                                    Delete
                                </button>
                            </form>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('users.update', $user->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_modal" value="edit">
                                    <input type="hidden" name="_edit_id" value="{{ $user->id }}">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if ($errors->any() && old('_modal') === 'edit' && old('_edit_id') == $user->id)
                                                <div class="alert alert-danger small">
                                                    <ul class="mb-0">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="mb-3">
                                                <label for="editName{{ $user->id }}" class="form-label">Name</label>
                                                <input id="editName{{ $user->id }}" name="name" class="form-control" value="{{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? old('name') : $user->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editEmail{{ $user->id }}" class="form-label">Email</label>
                                                <input id="editEmail{{ $user->id }}" name="email" type="email" class="form-control" value="{{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? old('email') : $user->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editRole{{ $user->id }}" class="form-label">Role</label>
                                                <select id="editRole{{ $user->id }}" name="role" class="form-select" required>
                                                    <option value="regular" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('role') === 'regular' ? 'selected' : '') : ($user->role === 'regular' ? 'selected' : '') }}>Regular</option>
                                                    <option value="admin" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('role') === 'admin' ? 'selected' : '') : ($user->role === 'admin' ? 'selected' : '') }}>Admin</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editPlanType{{ $user->id }}" class="form-label">Plan Type</label>
                                                <select id="editPlanType{{ $user->id }}" name="plan_type" class="form-select">
                                                    <option value="" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('plan_type') === '' ? 'selected' : '') : (empty($user->plan_type) ? 'selected' : '') }}>None</option>
                                                    <option value="starter" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('plan_type') === 'starter' ? 'selected' : '') : ($user->plan_type === 'starter' ? 'selected' : '') }}>Starter</option>
                                                    <option value="standard" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('plan_type') === 'standard' ? 'selected' : '') : ($user->plan_type === 'standard' ? 'selected' : '') }}>Standard</option>
                                                    <option value="pro" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('plan_type') === 'pro' ? 'selected' : '') : ($user->plan_type === 'pro' ? 'selected' : '') }}>Pro</option>
                                                    <option value="premium" {{ old('_modal') === 'edit' && old('_edit_id') == $user->id ? (old('plan_type') === 'premium' ? 'selected' : '') : ($user->plan_type === 'premium' ? 'selected' : '') }}>Premium</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 mb-5 d-flex justify-content-center">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <input type="hidden" name="_modal" value="create">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create')
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="createName" class="form-label">Name</label>
                        <input id="createName" name="name" class="form-control" value="{{ old('_modal') === 'create' ? old('name') : '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="createEmail" class="form-label">Email</label>
                        <input id="createEmail" name="email" type="email" class="form-control" value="{{ old('_modal') === 'create' ? old('email') : '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="createPassword" class="form-label">Password</label>
                        <input id="createPassword" name="password" type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="createRole" class="form-label">Role</label>
                        <select id="createRole" name="role" class="form-select" required>
                            <option value="regular" {{ old('_modal') === 'create' && old('role') === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="admin" {{ old('_modal') === 'create' && old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="createPlanType" class="form-label">Plan Type</label>
                        <select id="createPlanType" name="plan_type" class="form-select">
                            <option value="" {{ old('_modal') === 'create' && old('plan_type') === '' ? 'selected' : '' }}>None</option>
                            <option value="starter" {{ old('_modal') === 'create' && old('plan_type') === 'starter' ? 'selected' : '' }}>Starter</option>
                            <option value="standard" {{ old('_modal') === 'create' && old('plan_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="pro" {{ old('_modal') === 'create' && old('plan_type') === 'pro' ? 'selected' : '' }}>Pro</option>
                            <option value="premium" {{ old('_modal') === 'create' && old('plan_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Pagination buttons are hidden via CSS

        // Delete confirmation
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                const name = this.dataset.name;

                Swal.fire({
                    title: `Delete ${name}?`,
                    text: "This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Modal auto-open if validation errors exist
        @if ($errors->any())
            @if (old('_modal') === 'create')
                const createModalEl = document.getElementById('createUserModal');
                if (createModalEl) {
                    new bootstrap.Modal(createModalEl).show();
                }
            @elseif (old('_modal') === 'edit' && old('_edit_id'))
                const editModalEl = document.getElementById('editUserModal{{ old('_edit_id') }}');
                if (editModalEl) {
                    new bootstrap.Modal(editModalEl).show();
                }
            @endif
        @endif
    });
</script>
@endpush
