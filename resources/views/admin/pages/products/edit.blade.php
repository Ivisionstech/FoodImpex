@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('products.list') }}">Products</a> /
            Edit Product
        </h4>
        <div class="card">
            <h5 class="card-header">Edit Product</h5>
            <div class="card-body">
                <form novalidate class="ajax-form" action="{{ route('products.update', $product->uuid) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ $product->name }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sale_price">Sale Price</label>
                            <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                id="sale_price" name="sale_price" value="{{ $product->sale_price }}" required />
                            <div class="invalid-feedback" id="sale_price-error"></div>
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="form-label" for="purchase_price">Purchase Price</label>
                            <input type="number" class="form-control @error('purchase_price') is-invalid @enderror"
                                id="purchase_price" name="purchase_price" value="{{ $product->purchase_price }}" />
                            <div class="invalid-feedback" id="purchase_price-error"></div>
                        </div> --}}
                    </div>
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <label class="form-label" for="image">Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image"
                                name="image" />
                            <div class="invalid-feedback" id="image-error"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3">{{ $product->description }}</textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Update Product</button>
                            <a href="{{ route('products.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
