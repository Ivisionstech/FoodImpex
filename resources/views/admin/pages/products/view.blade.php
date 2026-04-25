@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Product Details</h4>
        
        <!-- Product Details Card -->
        <div class="card">
            <h5 class="card-header">Product Information</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" width="100" height="100" alt="Avatar"
                                class="rounded-circle mb-3" />
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                                <i class="bx bx-package" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Product Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $product->name }}
                            </div>
                        </div>
                        
                        <!-- Latest Purchase Information (NEW SECTION) -->
                        @php
                            $latestBillProduct = $product->billProducts()
                                ->with('bill')
                                ->latest()
                                ->first();
                        @endphp
                        
                        @if($latestBillProduct)
                        <div class="row mb-3 bg-light p-3 rounded">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Latest Purchase Details</h6>
                            </div>
                            <div class="col-md-4">
                                <strong>Net Weight (KG):</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-info">{{ number_format($latestBillProduct->net_weight, 2) }} KG</span>
                            </div>
                            <div class="col-md-4 mt-2">
                                <strong>Rate per 40 KG:</strong>
                            </div>
                            <div class="col-md-8 mt-2">
                                <span class="badge bg-success">Rs. {{ number_format($latestBillProduct->price, 2) }}</span>
                            </div>
                            <div class="col-md-4 mt-2">
                                <strong>Total Value:</strong>
                            </div>
                            <div class="col-md-8 mt-2">
                                @php
                                    $totalValue = $latestBillProduct->net_weight ? 
                                        ($latestBillProduct->net_weight * $latestBillProduct->price) / 40 : 0;
                                @endphp
                                <span class="badge bg-warning">Rs. {{ number_format($totalValue, 2) }}</span>
                            </div>
                            <div class="col-md-4 mt-2">
                                <strong>Purchase Date:</strong>
                            </div>
                            <div class="col-md-8 mt-2">
                                {{ $latestBillProduct->bill ? \Carbon\Carbon::parse($latestBillProduct->bill->date)->format('F j, Y') : '-' }}
                            </div>
                        </div>
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Purchase Price:</strong>
                            </div>
                            <div class="col-md-8">
                                Rs. {{ number_format($product->purchase_price, 2) }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Sale Price:</strong>
                            </div>
                            <div class="col-md-8">
                                Rs. {{ number_format($product->sale_price, 2) }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Current Stock (Bags):</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-primary">{{ $product->stock }} Bags</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $product->description ?: 'No description available' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock History Card -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Purchase History with Net Weight & Rate Details</h5>
                <a href="{{ route('products.list') }}" class="btn btn-secondary">Back to Products</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Quantity (Bags)</th>
                            <th>Net Weight (KG)</th>
                            <th>Rate/40 KG</th>
                            <th>Total Value</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $stockHistories = $product->stockHistories()
                                ->orderBy('date', 'desc')
                                ->get();
                            
                            $totalNetWeight = 0;
                            $totalValue = 0;
                        @endphp
                        
                        @forelse ($stockHistories as $stockHistory)
                            @php
                                // Find the corresponding bill product for this stock history entry
                                $billProduct = null;
                                if ($stockHistory->type == 'in') {
                                    $billProduct = $product->billProducts()
                                        ->where('quantity', $stockHistory->quantity)
                                        ->whereDate('created_at', '>=', $stockHistory->created_at->subMinutes(5))
                                        ->whereDate('created_at', '<=', $stockHistory->created_at->addMinutes(5))
                                        ->first();
                                }
                                
                                $netWeight = $billProduct->net_weight ?? 0;
                                $ratePer40kg = $billProduct->price ?? 0;
                                $rowValue = $netWeight ? ($netWeight * $ratePer40kg) / 40 : 0;
                                
                                if ($stockHistory->type == 'in') {
                                    $totalNetWeight += $netWeight;
                                    $totalValue += $rowValue;
                                }
                            @endphp
                            <tr>
                                <td>
                                    {{ $stockHistory->date ? \Carbon\Carbon::parse($stockHistory->date)->format('d-m-Y h:iA') : '-' }}
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $stockHistory->type == 'in' ? 'success' : 'danger' }}">
                                        {{ ucfirst($stockHistory->type) }}
                                    </span>
                                </td>
                                <td>{{ $stockHistory->description }}</td>
                                <td class="text-center">{{ $stockHistory->quantity }}</td>
                                <td class="text-end">
                                    @if($stockHistory->type == 'in' && $netWeight > 0)
                                        {{ number_format($netWeight, 2) }} KG
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($stockHistory->type == 'in' && $ratePer40kg > 0)
                                        Rs. {{ number_format($ratePer40kg, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($stockHistory->type == 'in' && $rowValue > 0)
                                        Rs. {{ number_format($rowValue, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $stockHistory->current_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No stock history found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($stockHistories) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Totals (Purchases):</th>
                            <th class="text-end">{{ number_format($totalNetWeight, 2) }} KG</th>
                            <th></th>
                            <th class="text-end">Rs. {{ number_format($totalValue, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        
        <!-- Purchase Bills Summary Card (NEW) -->
        @if($product->billProducts()->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">All Purchase Transactions</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Bill Date</th>
                            <th>Bill #</th>
                            <th>Vendor</th>
                            <th>Quantity (Bags)</th>
                            <th>Net Weight (KG)</th>
                            <th>Rate/40 KG</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->billProducts()->with(['bill.vendor'])->latest()->get() as $billProduct)
                            <tr>
                                <td>{{ $billProduct->bill ? \Carbon\Carbon::parse($billProduct->bill->date)->format('d-m-Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('vendors.bills.show', $billProduct->bill->uuid) }}" target="_blank">
                                        #{{ substr($billProduct->bill->uuid, 0, 8) }}
                                    </a>
                                </td>
                                <td>{{ $billProduct->bill->vendor->company_name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $billProduct->quantity }}</td>
                                <td class="text-end">{{ number_format($billProduct->net_weight, 2) }} KG</td>
                                <td class="text-end">Rs. {{ number_format($billProduct->price, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($billProduct->total_price, 2) }}</td>
                                <td>
                                    <a href="{{ route('vendors.bills.show', $billProduct->bill->uuid) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bx bx-show"></i> View Bill
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
@endsection