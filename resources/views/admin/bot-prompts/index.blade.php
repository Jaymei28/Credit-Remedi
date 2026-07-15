@extends('layouts.app')

@section('title', 'User Management')

@section('content')

<style>
    @media (min-width: 1200px) {
        .container {
            max-width: 1140px;
        }
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
                    <td colspan="5" class="text-center text-muted">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->links() }}
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

        // DataTables removed - using Laravel pagination instead
        // Table now uses Bootstrap dark theme styling

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
