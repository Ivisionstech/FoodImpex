@extends('admin.layout.master')
@section('content')
    <style>
        /* ============================================
           GRID VIEW STYLES
           ============================================ */
        .vendor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 10px 0;
        }
        
        .vendor-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .vendor-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.10);
            border-color: #c5cae9;
        }
        
        .vendor-card .card-header {
            padding: 16px 18px 10px 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #f1f3f5;
        }
        
        .vendor-card .card-header .vendor-name {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .vendor-card .card-header .vendor-name:hover {
            color: #696cff;
        }
        
        .vendor-card .card-body {
            padding: 14px 18px 18px 18px;
        }
        
        .vendor-card .card-body .info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        
        .vendor-card .card-body .info-row .label {
            color: #868e96;
            font-weight: 500;
        }
        
        .vendor-card .card-body .info-row .value {
            color: #212529;
            font-weight: 500;
        }
        
        .vendor-card .card-body .info-row .value .badge-balance {
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .badge-balance.positive {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-balance.negative {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-balance.zero {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .vendor-card .card-footer {
            padding: 10px 18px 14px 18px;
            border-top: 1px solid #f1f3f5;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        /* ============================================
           ACTION BUTTONS - PROFESSIONAL & COLORFUL
           ============================================ */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
            line-height: 1.4;
            min-width: 70px;
        }
        
        .btn-action i {
            font-size: 14px;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* View Button - Blue */
        .btn-view {
            background: #e7f1ff;
            color: #0d6efd;
        }
        .btn-view:hover {
            background: #0d6efd;
            color: #fff;
        }
        
        /* Edit Button - Amber */
        .btn-edit {
            background: #fff3e0;
            color: #f57c00;
        }
        .btn-edit:hover {
            background: #f57c00;
            color: #fff;
        }
        
        /* Delete Button - Red */
        .btn-delete {
            background: #fde8e8;
            color: #dc3545;
        }
        .btn-delete:hover {
            background: #dc3545;
            color: #fff;
        }
        
        /* Add Bill Button - Purple */
        .btn-addbill {
            background: #f3e8ff;
            color: #6f42c1;
        }
        .btn-addbill:hover {
            background: #6f42c1;
            color: #fff;
        }
        
        /* Download Ledger Button - Teal */
        .btn-downloadledger {
            background: #e0f7fa;
            color: #00838f;
        }
        .btn-downloadledger:hover {
            background: #00838f;
            color: #fff;
        }
        
        /* ============================================
           HEADER & FILTER
           ============================================ */
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 20px;
            background: #f8f9fc;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .list-header .title-section h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .list-header .title-section small {
            color: #868e96;
            font-size: 13px;
        }
        
        .btn-new-vendor {
            background: linear-gradient(135deg, #696cff 0%, #4a4dff 100%);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(105, 108, 255, 0.35);
        }
        
        .btn-new-vendor:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(105, 108, 255, 0.45);
            color: #fff;
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 640px) {
            .vendor-grid {
                grid-template-columns: 1fr;
            }
            
            .list-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .btn-new-vendor {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
            
            .vendor-card .card-footer {
                justify-content: center;
            }
        }
        
        @media (min-width: 641px) and (max-width: 992px) {
            .vendor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* ============================================
           PAGINATION STYLING
           ============================================ */
        .pagination-wrapper {
            padding: 16px 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .pagination-wrapper .pagination {
            justify-content: center;
            gap: 4px;
        }
        
        .pagination-wrapper .pagination .page-item .page-link {
            border-radius: 8px;
            border: none;
            color: #495057;
            padding: 8px 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .pagination-wrapper .pagination .page-item.active .page-link {
            background: #696cff;
            color: #fff;
            box-shadow: 0 2px 8px rgba(105, 108, 255, 0.3);
        }
        
        .pagination-wrapper .pagination .page-item .page-link:hover {
            background: #f1f3f5;
            transform: translateY(-1px);
        }
        
        /* Per page selector */
        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #868e96;
        }
        
        .per-page-selector select {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="list-header">
            <div class="title-section">
                <h5>
                    <i class='bx bx-building-house me-2 text-primary'></i>
                    Purchasers
                    <small class="text-muted ms-2">({{ $vendors->total() }} total)</small>
                </h5>
            </div>
            <div>
                <a href="{{ route('vendors.create') }}" class="btn-new-vendor">
                    <i class='bx bx-plus me-1'></i> New Purchaser
                </a>
            </div>
        </div>

        <!-- Grid View -->
        <div class="vendor-grid">
            @forelse ($vendors as $vendor)
                @php
                    $balance = floatval($vendor->balance ?? 0);
                    $balanceClass = $balance > 0 ? 'positive' : ($balance < 0 ? 'negative' : 'zero');
                @endphp
                <div class="vendor-card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <a href="{{ route('vendors.view', $vendor->uuid) }}" class="vendor-name" title="{{ $vendor->company_name }}">
                            {{ Str::limit($vendor->company_name, 22) }}
                        </a>
                        <span class="badge bg-label-secondary" style="font-size: 11px;">
                            #{{ $vendor->id }}
                        </span>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="info-row">
                            <span class="label"><i class='bx bx-user me-1'></i> Contact</span>
                            <span class="value">{{ $vendor->person_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label"><i class='bx bx-phone me-1'></i> Phone</span>
                            <span class="value">{{ $vendor->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e9ecef;">
                            <span class="label"><i class='bx bx-wallet me-1'></i> Balance</span>
                            <span class="value">
                                <span class="badge-balance {{ $balanceClass }}">
                                    PKR {{ number_format(abs($balance), 0) }}
                                    {{ $balance > 0 ? 'DR' : ($balance < 0 ? 'CR' : '') }}
                                </span>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Card Footer - Action Buttons -->
                    <div class="card-footer">
                        <!-- View -->
                        <a href="{{ route('vendors.view', $vendor->uuid) }}" 
                           class="btn-action btn-view" 
                           title="View Vendor">
                            <i class='bx bx-show'></i> View
                        </a>
                        
                        <!-- Edit -->
                        <a href="{{ route('vendors.edit', $vendor->uuid) }}" 
                           class="btn-action btn-edit" 
                           title="Edit Vendor">
                            <i class='bx bx-edit'></i> Edit
                        </a>
                        
                        <!-- Delete -->
                        <a href="#" 
                           class="btn-action btn-delete action-confirm1" 
                           data-url="{{ route('vendors.delete', $vendor->uuid) }}"
                           data-text="You want to delete this vendor!"
                           data-button-text="Yes, Delete it!"
                           title="Delete Vendor">
                            <i class='bx bx-trash'></i> Delete
                        </a>
                        
                        <!-- Add Bill -->
                        <a href="{{ route('vendors.bills.create', $vendor->uuid) }}" 
                           class="btn-action btn-addbill" 
                           title="Add Bill">
                            <i class='bx bx-receipt'></i> Bill
                        </a>
                        
                        <!-- Download Ledger -->
                        <a href="{{ route('vendors.bank-statement', ['uuid' => $vendor->uuid]) }}" 
                           class="btn-action btn-downloadledger" 
                           target="_blank"
                           title="Download Ledger">
                            <i class='bx bx-download'></i> Ledger
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-5" style="grid-column: 1 / -1;">
                    <i class='bx bx-building-house bx-lg text-muted'></i>
                    <h5 class="text-muted mt-3">No purchasers found</h5>
                    <p class="text-muted small">Click "New Purchaser" to add your first vendor.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($vendors->total() > 0)
            <div class="pagination-wrapper">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="per-page-selector">
                        <span>Show</span>
                        <select id="perPageSelect">
                            <option value="10" {{ $vendors->perPage() == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $vendors->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $vendors->perPage() == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $vendors->perPage() == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span>per page</span>
                    </div>
                    <div>
                        {{ $vendors->appends(request()->input())->links('pagination::bootstrap-5') }}
                    </div>
                    <div class="text-muted small">
                        Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Per page selector
            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                window.location.href = url.toString();
            });
        });
    </script>
    @endpush
@endsection