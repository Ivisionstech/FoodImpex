@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('customers.list') }}">Customers</a> /
            Edit Customer
        </h4>
        <div class="card">
            <h5 class="card-header">Edit Customer</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('customers.update', $customer->uuid) }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $customer->uuid }}">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Customer Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ $customer->name }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="person_name">Contact Person <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('person_name') is-invalid @enderror"
                                id="person_name" name="person_name" value="{{ $customer->person_name }}" required />
                            <div class="invalid-feedback" id="person_name-error"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ $customer->phone }}" required />
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ $customer->email }}" />
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" required
                                rows="3">{{ $customer->address }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Update Customer</button>
                            <a href="{{ route('customers.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
