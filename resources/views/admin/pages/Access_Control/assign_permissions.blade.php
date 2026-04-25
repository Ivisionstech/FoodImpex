@extends('admin.layout.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-2 mb-3">
        <span class="text-muted fw-light">Access Control /</span> Assign Permissions
    </h4>
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <div class="card">   
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Permissions for: <strong>{{ $user->name }}</strong></h5>
            <a href="{{ route('access-control.permissions.index') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Users
            </a>
        </div>
        
        <form action="{{ route('access-control.permissions.update', $user->id) }}" method="POST">
            @csrf
            <div class="card-body">
                <p class="text-muted mb-4">Select individual permissions to override or supplement the user's role-based access.</p>
                
                <div class="row">
                    @foreach($permissions as $permission)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card shadow-none border p-2">
                            <div class="form-check custom-option custom-option-basic">
                                <label class="form-check-label custom-option-content" for="perm-{{ $permission->id }}">
                                    <input class="form-check-input" type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->name }}" 
                                           id="perm-{{ $permission->id }}"
                                           {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                    
                                    <span class="custom-option-header">
                                        <span class="h6 mb-0 text-capitalize">
                                            {{ str_replace('-', ' ', $permission->name) }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer border-top text-end">
                <button type="reset" class="btn btn-label-secondary">Discard Changes</button>
                <button type="submit" class="btn btn-primary ms-2">Save Permissions</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    .form-check-input { cursor: pointer; }
    .form-check-label { cursor: pointer; width: 100%; display: block; }
    .card:hover { border-color: #696cff !important; transition: 0.3s; }
</style>
@endpush