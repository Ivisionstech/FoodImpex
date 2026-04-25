@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Cash</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cash</h5>
                @if (!$cash)
                    <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCashModal">Add Cash</a>
                @endif
            </div>
            @if ($cash)
                <div class="table-responsive text-nowrap" style="min-height: 320px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <tr>
                                <td>1</td>
                                <td><a href="{{ route('cash.view') }}">{{ $cash->balance }}</a>
                                </td>

                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('cash.view', $cash->uuid) }}">View</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
    <!-- Add Cash Modal -->
    <div class="modal fade" id="addCashModal" tabindex="-1" aria-labelledby="addCashModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="ajax-form" action="{{ route('cash.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCashModalLabel">Add Cash</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="balance" class="form-label">Balance</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="balance"
                                name="balance" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitButton" class="btn btn-primary">Add Cash</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
