@extends('admin.layout.master')
@section('content')
    <style>
        .balance-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .balance-help .text-danger {
            color: #dc3545 !important;
        }
        .balance-help .text-success {
            color: #28a745 !important;
        }
        .balance-preview {
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 8px;
            display: inline-block;
        }
        .balance-preview.positive {
            background-color: #d4edda;
            color: #155724;
        }
        .balance-preview.negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        .balance-preview.zero {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .invalid-feedback.d-block {
            display: block !important;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('vendors.list') }}">Vendors</a> /
            Add Vendor
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Vendor</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('vendors.store') }}" method="POST" enctype="multipart/form-data"
                    novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="company_name">Company Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                id="company_name" name="company_name" value="{{ old('company_name') }}" required />
                            <div class="invalid-feedback" id="company_name-error">@error('company_name'){{ $message }}@enderror</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="person_name">Person Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('person_name') is-invalid @enderror"
                                id="person_name" name="person_name" value="{{ old('person_name') }}" />
                            <div class="invalid-feedback" id="person_name-error">@error('person_name'){{ $message }}@enderror</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone') }}" />
                            <div class="invalid-feedback" id="phone-error">@error('phone'){{ $message }}@enderror</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" />
                            <div class="invalid-feedback" id="email-error">@error('email'){{ $message }}@enderror</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="balance">Opening Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">PKR</span>
                                <input type="number" step="0.01" 
                                    class="form-control @error('balance') is-invalid @enderror" 
                                    id="balance" name="balance" 
                                    value="{{ old('balance') ?? 0 }}" 
                                    oninput="updateBalancePreview(this.value)" />
                            </div>
                            <div id="balancePreview" class="balance-preview zero">
                                Balance: PKR 0.00 (Zero Balance)
                            </div>
                            @error('balance')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="open_balance_date">Opening Balance Date</label>
                            <input type="datetime-local"
                                class="form-control @error('open_balance_date') is-invalid @enderror" 
                                id="open_balance_date" name="open_balance_date" 
                                value="{{ old('open_balance_date') }}" />
                            <div class="invalid-feedback" id="open_balance_date-error">@error('open_balance_date'){{ $message }}@enderror</div>
                            <div class="balance-help">
                                <small>Default: Current date & time</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="profile">Profile</label>
                            <input type="file" class="form-control @error('profile') is-invalid @enderror" 
                                id="profile" name="profile" accept="image/*" />
                            @error('profile')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="balance-help">
                                <small>Supported: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            <div class="invalid-feedback" id="address-error">@error('address'){{ $message }}@enderror</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Create Vendor
                            </button>
                            <a href="{{ route('vendors.list') }}" class="btn btn-secondary">
                                <i class='bx bx-x me-1'></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateBalancePreview(value) {
            const preview = document.getElementById('balancePreview');
            const numValue = parseFloat(value) || 0;
            
            // Remove existing classes
            preview.classList.remove('positive', 'negative', 'zero');
            
            if (numValue > 0) {
                preview.classList.add('positive');
                preview.innerHTML = `<i class='bx bx-chevron-up-circle me-1'></i> Balance: PKR ${numValue.toFixed(2)} (Vendor owes us - CREDIT)`;
            } else if (numValue < 0) {
                preview.classList.add('negative');
                preview.innerHTML = `<i class='bx bx-chevron-down-circle me-1'></i> Balance: PKR ${Math.abs(numValue).toFixed(2)} (We owe vendor - DEBIT)`;
            } else {
                preview.classList.add('zero');
                preview.innerHTML = `<i class='bx bx-minus-circle me-1'></i> Balance: PKR 0.00 (Zero Balance)`;
            }
        }

        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            const balanceInput = document.getElementById('balance');
            if (balanceInput) {
                updateBalancePreview(balanceInput.value);
            }
        });
    </script>
@endsection