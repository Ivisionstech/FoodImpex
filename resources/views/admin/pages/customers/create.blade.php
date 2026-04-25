@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('customers.list') }}">Customers</a> /
            Add Customer
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Customer</h5>
            <div class="card-body">
                <form novalidate class="ajax-form" action="{{ route('customers.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Customer Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="person_name">Contact Person</label>
                            <input type="text" class="form-control @error('person_name') is-invalid @enderror"
                                id="person_name" name="person_name" value="{{ old('person_name') }}" />
                            <div class="invalid-feedback" id="person_name-error"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="balance">Open Balance</label>
                            <input type="number" class="form-control @error('balance') is-invalid @enderror" id="balance"
                                name="balance" value="0" />
                            <div class="invalid-feedback" id="balance-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="open_balance_date">Open Balance Date</label>
                            <input type="datetime-local"
                                class="form-control @error('open_balance_date') is-invalid @enderror" id="open_balance_date"
                                name="open_balance_date" value="{{ old('open_balance_date') }}" />
                            <div class="invalid-feedback" id="open_balance_date-error"></div>
                        </div>


                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone') }}" />
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" />
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Create Customer</button>
                            <a href="{{ route('customers.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
