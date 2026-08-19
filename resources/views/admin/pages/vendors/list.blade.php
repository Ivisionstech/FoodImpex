@extends('admin.layout.master')
@section('content')
    <style>
        /* ============================================
           PAGE HEADER
           ============================================ */
        .page-header-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 24px 30px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.35);
        }
        
        .page-header-modern .header-left h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 22px;
        }
        
        .page-header-modern .header-left h4 i {
            margin-right: 10px;
        }
        
        .page-header-modern .header-left .subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin-top: 4px;
        }
        
        .page-header-modern .header-right .btn-new {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 10px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .page-header-modern .header-right .btn-new:hover {
            background: #fff;
            color: #667eea;
            border-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.10);
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .stat-card .stat-icon.primary { background: #e7f1ff; color: #0d6efd; }
        .stat-card .stat-icon.success { background: #d4edda; color: #28a745; }
        .stat-card .stat-icon.warning { background: #fff3e0; color: #f57c00; }
        .stat-card .stat-icon.danger { background: #fde8e8; color: #dc3545; }
        .stat-card .stat-icon.info { background: #e0f7fa; color: #00838f; }
        
        .stat-card .stat-info .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
        }
        
        .stat-card .stat-info .stat-label {
            font-size: 13px;
            color: #868e96;
            font-weight: 500;
        }
        
        /* ============================================
           GRID VIEW
           ============================================ */
        .vendor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .vendor-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #eef0f3;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
        }
        
        .vendor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .vendor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
            border-color: #d0d5dd;
        }
        
        .vendor-card:hover::before {
            opacity: 1;
        }
        
        .vendor-card .card-top {
            padding: 20px 22px 0 22px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .vendor-card .card-top .vendor-name {
            font-size: 17px;
            font-weight: 600;
            color: #1a1a2e;
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .vendor-card .card-top .vendor-name:hover {
            color: #667eea;
        }
        
        .vendor-card .card-top .vendor-name .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .vendor-card .card-top .vendor-id {
            font-size: 11px;
            color: #adb5bd;
            background: #f1f3f5;
            padding: 2px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .vendor-card .card-body {
            padding: 14px 22px 18px 22px;
        }
        
        .vendor-card .card-body .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 14px;
        }
        
        .vendor-card .card-body .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            padding: 4px 0;
        }
        
        .vendor-card .card-body .info-item .label {
            color: #adb5bd;
            font-weight: 500;
            min-width: 44px;
        }
        
        .vendor-card .card-body .info-item .value {
            color: #212529;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .vendor-card .card-body .balance-section {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px dashed #f1f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .vendor-card .card-body .balance-section .balance-label {
            font-size: 13px;
            color: #868e96;
            font-weight: 500;
        }
        
        .vendor-card .card-body .balance-section .balance-value {
            font-size: 20px;
            font-weight: 700;
        }
        
        .vendor-card .card-body .balance-section .balance-value.positive {
            color: #28a745;
        }
        
        .vendor-card .card-body .balance-section .balance-value.negative {
            color: #dc3545;
        }
        
        .vendor-card .card-body .balance-section .balance-value.zero {
            color: #6c757d;
        }
        
        .vendor-card .card-body .balance-section .balance-value .dr-cr {
            font-size: 13px;
            font-weight: 600;
            background: #f1f3f5;
            padding: 2px 10px;
            border-radius: 50px;
            margin-left: 6px;
        }
        
        /* ============================================
           ACTION BUTTONS
           ============================================ */
        .vendor-card .card-actions {
            padding: 12px 22px 18px 22px;
            border-top: 1px solid #f1f3f5;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .btn-action-modern {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
            line-height: 1.4;
            flex: 1 0 auto;
            min-width: 56px;
        }
        
        .btn-action-modern i {
            font-size: 15px;
        }
        
        .btn-action-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }
        
        /* Individual Button Colors */
        .btn-view-modern {
            background: linear-gradient(135deg, #e7f1ff, #d4e4ff);
            color: #0d6efd;
        }
        .btn-view-modern:hover {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: #fff;
        }
        
        .btn-edit-modern {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #f57c00;
        }
        .btn-edit-modern:hover {
            background: linear-gradient(135deg, #f57c00, #e65100);
            color: #fff;
        }
        
        .btn-delete-modern {
            background: linear-gradient(135deg, #fde8e8, #fccccc);
            color: #dc3545;
        }
        .btn-delete-modern:hover {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
        }
        
        .btn-bill-modern {
            background: linear-gradient(135deg, #f3e8ff, #e8d5ff);
            color: #6f42c1;
        }
        .btn-bill-modern:hover {
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            color: #fff;
        }
        
        .btn-ledger-modern {
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #00838f;
        }
        .btn-ledger-modern:hover {
            background: linear-gradient(135deg, #00838f, #006064);
            color: #fff;
        }
        
        /* ============================================
           PAGINATION
           ============================================ */
        .pagination-wrapper-modern {
            margin-top: 30px;
            padding: 20px 24px;
            background: #f8f9fc;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .pagination-wrapper-modern .pagination-info {
            color: #868e96;
            font-size: 14px;
        }
        
        .pagination-wrapper-modern .pagination-info strong {
            color: #1a1a2e;
        }
        
        .pagination-wrapper-modern .pagination {
            margin: 0;
            gap: 4px;
        }
        
        .pagination-wrapper-modern .pagination .page-item .page-link {
            border-radius: 10px;
            border: none;
            color: #495057;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s;
            background: transparent;
        }
        
        .pagination-wrapper-modern .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.35);
        }
        
        .pagination-wrapper-modern .pagination .page-item .page-link:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .pagination-wrapper-modern .per-page-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #868e96;
        }
        
        .pagination-wrapper-modern .per-page-selector select {
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
            outline: none;
        }
        
        .pagination-wrapper-modern .per-page-selector select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 640px) {
            .vendor-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header-modern {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .page-header-modern .header-right .btn-new {
                width: 100%;
                justify-content: center;
            }
            
            .vendor-card .card-body .info-grid {
                grid-template-columns: 1fr;
            }
            
            .vendor-card .card-actions {
                justify-content: center;
            }
            
            .btn-action-modern {
                flex: 0 1 auto;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .pagination-wrapper-modern {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .pagination-wrapper-modern .per-page-selector {
                justify-content: center;
            }
        }
        
        @media (min-width: 641px) and (max-width: 992px) {
            .vendor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* ============================================
           EMPTY STATE
           ============================================ */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: #fafbfc;
            border-radius: 18px;
            border: 2px dashed #e9ecef;
        }
        
        .empty-state .empty-icon {
            font-size: 64px;
            color: #d0d5dd;
            margin-bottom: 16px;
        }
        
        .empty-state h5 {
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #868e96;
            max-width: 400px;
            margin: 0 auto;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- ============================================
        MODERN PAGE HEADER
        ============================================ -->
        <div class="page-header-modern">
            <div class="header-left">
                <h4>
                    <i class='bx bx-building-house'></i> Purchasers
                    <span style="font-size: 14px; font-weight: 400; opacity: 0.7; margin-left: 8px;">
                        ({{ $vendors->total() }} total)
                    </span>
                </h4>
                <div class="subtitle">
                    <i class='bx bx-user me-1'></i> Manage all your vendors and suppliers
                </div>
            </div>
            <div class="header-right">
                <a href="{{ route('vendors.create') }}" class="btn-new">
                    <i class='bx bx-plus-circle'></i> New Purchaser
                </a>
            </div>
        </div>

        <!-- ============================================
        STATS CARDS
        ============================================ -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon primary"><i class='bx bx-group'></i></div>
                <div class="stat-info">
                    <div class="stat-number">{{ $vendors->total() }}</div>
                    <div class="stat-label">Total Purchasers</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success"><i class='bx bx-wallet'></i></div>
                <div class="stat-info">
                    <div class="stat-number">
                        PKR {{ number_format($vendors->sum('balance'), 0) }}
                    </div>
                    <div class="stat-label">Total Balance</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning"><i class='bx bx-line-chart'></i></div>
                <div class="stat-info">
                    <div class="stat-number">
                        @php
                            $positive = $vendors->filter(fn($v) => $v->balance > 0)->count();
                        @endphp
                        {{ $positive }}
                    </div>
                    <div class="stat-label">With Balance (DR)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger"><i class='bx bx-trending-down'></i></div>
                <div class="stat-info">
                    <div class="stat-number">
                        @php
                            $negative = $vendors->filter(fn($v) => $v->balance < 0)->count();
                        @endphp
                        {{ $negative }}
                    </div>
                    <div class="stat-label">With Balance (CR)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info"><i class='bx bx-file'></i></div>
                <div class="stat-info">
                    <div class="stat-number">
                        @php
                            $zero = $vendors->filter(fn($v) => $v->balance == 0)->count();
                        @endphp
                        {{ $zero }}
                    </div>
                    <div class="stat-label">Zero Balance</div>
                </div>
            </div>
        </div>

        <!-- ============================================
        GRID VIEW
        ============================================ -->
        <div class="vendor-grid">
            @forelse ($vendors as $vendor)
                @php
                    $balance = floatval($vendor->balance ?? 0);
                    $balanceClass = $balance > 0 ? 'positive' : ($balance < 0 ? 'negative' : 'zero');
                    $drCr = $balance > 0 ? 'DR' : ($balance < 0 ? 'CR' : '');
                    $initial = strtoupper(substr($vendor->company_name, 0, 1));
                @endphp
                <div class="vendor-card">
                    <!-- Card Top -->
                    <div class="card-top">
                        <a href="{{ route('vendors.view', $vendor->uuid) }}" class="vendor-name">
                            <span class="avatar">{{ $initial }}</span>
                            {{ Str::limit($vendor->company_name, 20) }}
                        </a>
                        <span class="vendor-id">#{{ $vendor->id }}</span>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label"><i class='bx bx-user'></i></span>
                                <span class="value">{{ $vendor->person_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label"><i class='bx bx-phone'></i></span>
                                <span class="value">{{ $vendor->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <span class="label"><i class='bx bx-envelope'></i></span>
                                <span class="value">{{ $vendor->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <!-- Balance Section -->
                        <div class="balance-section">
                            <span class="balance-label"><i class='bx bx-wallet'></i> Current Balance</span>
                            <span class="balance-value {{ $balanceClass }}">
                                PKR {{ number_format(abs($balance), 0) }}
                                @if($drCr)
                                    <span class="dr-cr">{{ $drCr }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <!-- Card Actions -->
                    <div class="card-actions">
                        <a href="{{ route('vendors.view', $vendor->uuid) }}" 
                           class="btn-action-modern btn-view-modern" 
                           title="View Vendor">
                            <i class='bx bx-show'></i> View
                        </a>
                        
                        <a href="{{ route('vendors.edit', $vendor->uuid) }}" 
                           class="btn-action-modern btn-edit-modern" 
                           title="Edit Vendor">
                            <i class='bx bx-edit'></i> Edit
                        </a>
                        
                        <a href="#" 
                           class="btn-action-modern btn-delete-modern action-confirm1" 
                           data-url="{{ route('vendors.delete', $vendor->uuid) }}"
                           data-text="You want to delete this vendor!"
                           data-button-text="Yes, Delete it!"
                           title="Delete Vendor">
                            <i class='bx bx-trash'></i> 
                        </a>
                        
                        <a href="{{ route('vendors.bills.create', $vendor->uuid) }}" 
                           class="btn-action-modern btn-bill-modern" 
                           title="Add Bill">
                            <i class='bx bx-receipt'></i> Bill
                        </a>
                        
                        <a href="{{ route('vendors.bank-statement', ['uuid' => $vendor->uuid]) }}" 
                           class="btn-action-modern btn-ledger-modern" 
                           target="_blank"
                           title="Download Ledger">
                            <i class='bx bx-download'></i> Ledger
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class='bx bx-building-house'></i></div>
                    <h5>No Purchasers Found</h5>
                    <p>Click the <strong>"New Purchaser"</strong> button to add your first vendor to the system.</p>
                </div>
            @endforelse
        </div>

        <!-- ============================================
        PAGINATION
        ============================================ -->
        @if($vendors->total() > 0)
            <div class="pagination-wrapper-modern">
                <div class="pagination-info">
                    Showing <strong>{{ $vendors->firstItem() }}</strong> to 
                    <strong>{{ $vendors->lastItem() }}</strong> of 
                    <strong>{{ $vendors->total() }}</strong> purchasers
                </div>
                <div>
                    {{ $vendors->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
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