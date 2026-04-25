@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">

            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-package" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Products</span>
                        <h3 class="card-title mb-2">{{ $products->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-file" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Quotations</span>
                        <h3 class="card-title mb-2">{{ $quotations->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-user" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Vendors</span>
                        <h3 class="card-title mb-2">{{ $vendors->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-group" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Customers</span>
                        <h3 class="card-title mb-2">{{ $customers->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-money" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Vendors Balance</span>
                        <h3 class="card-title mb-2">PKR {{ $sendingBalance }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-money" style="font-size: 40px; color: #deba24;"></i>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Customers Balance</span>
                        <h3 class="card-title mb-2">PKR {{ $receivingBalance }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
