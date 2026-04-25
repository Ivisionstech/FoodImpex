@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('daybooks.list') }}">Daybooks</a> /
            Add Daybook History
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Daybook History</h5>
            <div class="card-body">
                <form novalidate class="ajax-form" action="{{ route('daybooks.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="expense_date">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('expense_date') is-invalid @enderror"
                                id="expense_date" name="expense_date" value="{{ now() }}" required />
                            <div class="invalid-feedback" id="expense_date-error"></div>
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="form-label" for="status">Type</label>
                            <select name="status" id="status" required
                                class="form-control @error('status') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="Household">Household</option>
                                <option value="Business">Business</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="invalid-feedback" id="status-error"></div>
                        </div> --}}
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                                name="amount" value="{{ old('amount') }}" required />
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" id="description" required class="form-control @error('description') is-invalid @enderror"
                            rows="3">{{ old('description') }}</textarea>
                        <div class="invalid-feedback" id="description-error"></div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Create Daybook History</button>
                            <a href="{{ route('daybooks.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
