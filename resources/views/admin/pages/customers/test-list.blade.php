@extends('admin.layout.master')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5>Test Bills List ({{ $bills->total() }} total)</h5>
        </div>
        <div class="card-body">
            @if($bills->isEmpty())
                <div class="alert alert-warning">No bills found!</div>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>UUID</th>
                            <th>Customer ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                        <tr>
                            <td>{{ $bill->id }}</td>
                            <td>{{ $bill->uuid }}</td>
                            <td>{{ $bill->customer_id }}</td>
                            <td>{{ $bill->bill_date }}</td>
                            <td>{{ $bill->total_amount }}</td>
                            <td>{{ $bill->type }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection