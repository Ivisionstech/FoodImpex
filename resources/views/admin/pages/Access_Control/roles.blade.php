@extends('admin.layout.master')

{{-- Formatting ko tight aur compact karne ke liye CSS --}}
@push('css')
<style>
    .table td, .table th {
        padding: 0.6rem 1.2rem !important;
        vertical-align: middle !important;
    }
    .card-header {
        padding: 1rem 1.25rem !important;
    }
    .table-responsive {
        min-height: 200px !important;
    }
</style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-2 mb-3"><span class="text-muted fw-light">Dashboard /</span> Access Control</h4>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-2">
                <h5 class="mb-0">Roles Management</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="bx bx-plus me-1"></i> Add New Role
                </button>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="rolesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Role Name</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="fw-semibold">{{ $role->name }}</span></td>
                                <td>{{ $role->created_at->format('d-m-Y') }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" type="button" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" 
                                                   data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                                   <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('access-control.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Role Modal -->
                            <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('access-control.roles.update', $role->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Role</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Role Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('access-control.roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Create New Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Role Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Popup alerts ko block karne ke liye
    if (typeof $.fn.dataTable !== 'undefined') {
        $.fn.dataTable.ext.errMode = 'none'; 
    }
</script>
@endpush