@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('vendors.list') }}">Vendors</a> /
            Edit Vendor
        </h4>
        <div class="card">
            <h5 class="card-header">Edit Vendor</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('vendors.update', $vendor->uuid) }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="company_name">Company Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                id="company_name" name="company_name" value="{{ $vendor->company_name }}" required />
                            <div class="invalid-feedback" id="company_name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="person_name">Person Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('person_name') is-invalid @enderror"
                                id="person_name" name="person_name" value="{{ $vendor->person_name }}" required />
                            <div class="invalid-feedback" id="person_name-error"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ $vendor->phone }}" required />
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ $vendor->email }}" />
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                    </div>
                    <div class="row mb-3">

                        <div class="col-6">
                            <label class="form-label" for="profile">Profile</label>
                            <input type="file" class="form-control @error('profile') is-invalid @enderror" id="profile"
                                name="profile" />
                            <div class="invalid-feedback" id="profile-error"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" required
                                rows="3">{{ $vendor->address }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Update Vendor</button>
                            <a href="{{ route('vendors.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
