@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('vendors.list') }}">Banks</a> /
            Edit Bank
        </h4>
        <div class="card">
            <h5 class="card-header">Edit Bank</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('banks.update', $bank->uuid) }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ $bank->name }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="code">Account Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_title') is-invalid @enderror"
                                id="account_title" name="account_title" value="{{ $bank->account_title }}" />
                            <div class="invalid-feedback" id="account_title-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_number">Account Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                id="account_number" name="account_number" value="{{ $bank->account_number }}" />
                            <div class="invalid-feedback" id="account_number-error"></div>
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="form-label" for="account_balance">Account Balance <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('account_balance') is-invalid @enderror"
                                id="account_balance" name="account_balance" value="{{ $bank->account_balance }}" />
                            <div class="invalid-feedback" id="account_balance-error"></div>
                        </div> --}}
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Update Bank</button>
                            <a href="{{ route('banks.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
