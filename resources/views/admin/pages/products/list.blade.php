@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Header with Stats -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <span class="text-muted fw-light">Dashboard /</span> Products Inventory
                </h4>
                <p class="text-muted small mb-0">Manage your products and track weights & pricing</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.create') }}" class="btn btn-primary d-none">Add Product</a>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="bx bx-info-circle me-1"></i> Info
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <div class="avatar-initial rounded-3 bg-label-primary" style="width: 56px; height: 56px;">
                                    <i class="bx bx-package fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Total Products</span>
                                <h3 class="fw-bold mt-1 mb-0">{{ $products->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <div class="avatar-initial rounded-3 bg-label-info" style="width: 56px; height: 56px;">
                                    <i class="bx bx-weight fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Avg Net Weight</span>
                                <h4 class="fw-bold mt-1 mb-0 text-info">{{ number_format($products->avg('net_weight') ?? 0, 2) }} KG</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <div class="avatar-initial rounded-3 bg-label-success" style="width: 56px; height: 56px;">
                                    <i class="bx bx-line-chart fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Avg Price/40kg</span>
                                <h4 class="fw-bold mt-1 mb-0 text-success">PKR {{ number_format($products->avg('price_40kg') ?? 0, 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="bx bx-package me-2 text-primary"></i>
                        All Products
                    </h5>
                    <span class="badge bg-primary">{{ $products->count() }} Records</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="min-width: 1000px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-semibold" style="width: 50px;">#</th>
                            <th class="py-3 text-muted small fw-semibold" style="width: 80px;">IMAGE</th>
                            <th class="py-3 text-muted small fw-semibold" style="width: 350px;">PRODUCT NAME</th>
                            <th class="py-3 text-muted small fw-semibold" style="width: 120px;">NET WEIGHT</th>
                            <th class="py-3 text-muted small fw-semibold" style="width: 150px;">PRICE/40KG</th>
                            <th class="py-3 text-muted small fw-semibold text-end px-4" style="width: 250px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td class="px-4">
                                    <span class="fw-semibold text-muted">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             width="50" height="50" 
                                             alt="Product Image"
                                             style="border-radius: 8px; object-fit: cover;"
                                             class="border">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; border-radius: 8px;">
                                            <i class="bx bx-image text-muted" style="font-size: 24px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.view', $product->uuid) }}" class="text-decoration-none fw-semibold text-dark">
                                        {{ $product->name }}
                                    </a>
                                    @if($product->description)
                                        <br><small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold text-info">{{ number_format($product->net_weight ?? 0, 2) }}</span>
                                        <span class="text-muted ms-1 small">KG</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold text-primary">PKR {{ number_format($product->price_40kg ?? 0, 2) }}</span>
                                    </div>
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <!-- <a href="{{ route('products.view', $product->uuid) }}" 
                                           class="btn btn-sm btn-icon btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="View Product">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->uuid) }}" 
                                           class="btn btn-sm btn-icon btn-outline-warning" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Product">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-icon btn-outline-success add-stock" 
                                                data-uuid="{{ $product->uuid }}"
                                                data-sale_price="{{ $product->sale_price }}"
                                                data-bs-toggle="tooltip" 
                                                title="Add Stock">
                                            <i class="bx bx-plus-circle"></i>
                                        </button> -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-outline-secondary" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#"><i class="bx bx-history me-2"></i>Stock History</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="bx bx-bar-chart-alt-2 me-2"></i>Analytics</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('{{ $product->uuid }}')"><i class="bx bx-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-0 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing all {{ $products->count() }} products
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Net Weight:</strong> The latest net weight recorded from purchase bills</p>
                    <p><strong>Price per 40kg:</strong> The latest purchase rate per 40 kilograms</p>
                    <p class="mb-0">These values are automatically updated when you create purchase bills.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="ajax-form" action="{{ route('products.add-stock') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="uuid" id="uuid">
                        <div class="form-group mb-3">
                            <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                            <div class="invalid-feedback" id="stock-error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="sale_price" class="form-label fw-semibold">Sale Price</label>
                            <input type="number" class="form-control" id="sale_price" name="sale_price" required>
                            <div class="invalid-feedback" id="sale_price-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitButton">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add Stock button click handler
            $('.add-stock').click(function() {
                var uuid = $(this).data('uuid');
                var salePrice = $(this).data('sale_price');
                $('#addStockModal').modal('show');
                $('#uuid').val(uuid);
                $('#sale_price').val(salePrice);
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Delete confirmation
        function confirmDelete(uuid) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const form = document.getElementById('delete-form');
                form.action = `/products/delete/${uuid}`;
                form.submit();
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
@endpush