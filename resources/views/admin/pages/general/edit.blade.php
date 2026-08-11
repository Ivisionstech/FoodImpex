@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <!-- Journal Entry Header -->
                <div class="card mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="avatar avatar-sm bg-label-warning rounded">
                                            <i class="bx bx-edit fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Edit General Entry #{{ $entry->id }}</h5>
                                        <small class="text-muted">Update existing general entry</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted mb-1">Entry Date</label>
                                        <input type="date" class="form-control form-control-lg" id="transaction_date" name="transaction_date" value="{{ \Carbon\Carbon::parse($entry->transaction_date)->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="badge bg-{{ $entry->approval_status == 'approved' ? 'success' : 'warning' }} p-2">
                                            <i class="bx bx-{{ $entry->approval_status == 'approved' ? 'check-circle' : 'time' }} me-1"></i>
                                            {{ ucfirst($entry->approval_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Entries Container -->
                <div class="card">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Entries</h5>
                        <button type="button" class="btn btn-primary" id="addRowBtn">
                            <i class="bx bx-plus-circle me-1"></i> Add New Entry
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <form action="{{ route('general-transactions.update', $entry->id) }}" method="POST" id="generalEntryForm">
                            @csrf
                            @method('PUT')
                            
                            <input type="hidden" name="transaction_date" id="hidden_transaction_date" value="{{ \Carbon\Carbon::parse($entry->transaction_date)->format('Y-m-d') }}">
                            
                            <div id="journalEntriesContainer">
                                <!-- Existing entries will be populated here -->
                            </div>

                            <!-- Totals Section -->
                            <div class="row mt-4">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="fw-semibold">Total Debit</span>
                                                <span class="fw-bold text-danger" id="totalDebitDisplay">PKR 0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="fw-semibold">Total Credit</span>
                                                <span class="fw-bold text-success" id="totalCreditDisplay">PKR 0.00</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between mt-2">
                                                <span class="fw-semibold">Difference</span>
                                                <span class="fw-bold" id="totalDifferenceDisplay">PKR 0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <a href="{{ route('general-transactions.index') }}" class="btn btn-outline-secondary me-2">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-warning" id="submitBtn">
                                        <i class="bx bx-save me-1"></i> Update Entry
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .journal-entry-row {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            transition: all 0.2s ease-in-out;
            position: relative;
        }
        
        .journal-entry-row:hover {
            border-color: #c5cae9;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        }
        
        .entry-number {
            position: absolute;
            top: -10px;
            left: 10px;
            background: #696cff;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            z-index: 1;
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 42px;
            border-radius: 0.5rem;
            border-color: #e9ecef;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single {
            background-color: #fff;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 1rem;
            color: #495057;
        }
        
        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 0.5rem;
            border-color: #e9ecef;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .amount-input {
            font-size: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
            height: 42px;
        }
        
        .amount-input:focus {
            background-color: #fff8e1;
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
        }
        
        .remove-row-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .remove-row-btn:hover {
            transform: scale(1.05);
        }
        
        .form-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        #addRowBtn {
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .journal-entry-row {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>

    <script>
        $(document).ready(function() {
            let rowCounter = 0;
            let rowData = [];

            function customMatcher(params, data) {
                if ($.trim(params.term) === '') return data;
                
                var searchTerm = params.term.toLowerCase().trim();
                var searchWords = searchTerm.split(/\s+/);
                var text = data.text.toLowerCase();
                var matchesAllWords = searchWords.every(function(word) { return text.indexOf(word) !== -1; });
                
                if (matchesAllWords) return data;
                
                var $option = $(data.element);
                var customName = ($option.data('name') || '').toLowerCase();
                var customType = ($option.data('type') || '').toLowerCase();
                var customBalance = ($option.data('balance') || '').toString().toLowerCase();
                var matchesInCustom = searchWords.every(function(word) {
                    return customName.indexOf(word) !== -1 || customType.indexOf(word) !== -1 || customBalance.indexOf(word) !== -1;
                });
                
                return matchesInCustom ? data : null;
            }
            
            function formatAccountResult(option, searchTerm) {
                if (!option.id) return option.text;
                
                var $option = $(option.element);
                var type = $option.data('type');
                var balance = $option.data('balance');
                var icon = '';
                var badge = '';
                
                switch(type) {
                    case 'customer': icon = '<i class="fas fa-users" style="color: #0d6efd;"></i>'; badge = '<span class="badge bg-primary ms-2" style="font-size: 10px;">Customer</span>'; break;
                    case 'vendor': icon = '<i class="fas fa-truck" style="color: #198754;"></i>'; badge = '<span class="badge bg-success ms-2" style="font-size: 10px;">Vendor</span>'; break;
                    case 'bank': icon = '<i class="fas fa-university" style="color: #6f42c1;"></i>'; badge = '<span class="badge bg-purple ms-2" style="font-size: 10px; background-color: #6f42c1;">Bank</span>'; break;
                    case 'cash': icon = '<i class="fas fa-money-bill-wave" style="color: #fd7e14;"></i>'; badge = '<span class="badge bg-warning ms-2" style="font-size: 10px;">Cash</span>'; break;
                    default: icon = '<i class="fas fa-building"></i>'; badge = '';
                }
                
                var balanceHtml = balance !== undefined && balance !== null ? `<small class="text-muted ms-2">Balance: PKR ${parseFloat(balance).toLocaleString()}</small>` : '';
                return $(`<div class="d-flex align-items-center justify-content-between w-100"><div>${icon} <span>${option.text}</span>${badge}${balanceHtml}</div></div>`);
            }
            
            function formatAccountSelection(option) {
                if (!option.id) return option.text;
                var $option = $(option.element);
                var type = $option.data('type');
                var icon = '';
                switch(type) {
                    case 'customer': icon = '<i class="fas fa-users me-2" style="color: #0d6efd;"></i>'; break;
                    case 'vendor': icon = '<i class="fas fa-truck me-2" style="color: #198754;"></i>'; break;
                    case 'bank': icon = '<i class="fas fa-university me-2" style="color: #6f42c1;"></i>'; break;
                    case 'cash': icon = '<i class="fas fa-money-bill-wave me-2" style="color: #fd7e14;"></i>'; break;
                    default: icon = '<i class="fas fa-building me-2"></i>';
                }
                return $(`<span>${icon} ${option.text}</span>`);
            }

            function initSelect2(element) {
                if (element && !$(element).data('select2')) {
                    $(element).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: '🔍 Search by name...',
                        allowClear: true,
                        dropdownParent: $(element).closest('.journal-entry-row'),
                        matcher: customMatcher,
                        templateResult: function(option) { return formatAccountResult(option, ''); },
                        templateSelection: formatAccountSelection
                    });
                }
            }

            $('#transaction_date').on('change', function() {
                $('#hidden_transaction_date').val($(this).val());
            });

            function createNewRow(rowId, isFirst = false, savedData = null) {
                let accountValue = savedData ? savedData.account : '';
                let debitValue = savedData ? savedData.debit : '0';
                let creditValue = savedData ? savedData.credit : '0';
                let descriptionValue = savedData ? savedData.description : '';
                
                let customersHtml = '';
                @if(isset($customers) && $customers->count() > 0)
                    @foreach ($customers as $customer)
                        customersHtml += `<option value="customer_{{ $customer->id }}" data-type="customer" data-name="{{ $customer->name }}" data-balance="{{ $customer->balance ?? 0 }}" ${accountValue == 'customer_{{ $customer->id }}' ? 'selected' : ''}>👥 {{ $customer->name }} (Customer) - Balance: PKR {{ number_format($customer->balance ?? 0, 2) }}</option>`;
                    @endforeach
                @endif
                
                let vendorsHtml = '';
                @if(isset($vendors) && $vendors->count() > 0)
                    @foreach ($vendors as $vendor)
                        vendorsHtml += `<option value="vendor_{{ $vendor->id }}" data-type="vendor" data-name="{{ $vendor->company_name }}" data-balance="{{ $vendor->balance ?? 0 }}" ${accountValue == 'vendor_{{ $vendor->id }}' ? 'selected' : ''}>🚚 {{ $vendor->company_name }} (Vendor) - Balance: PKR {{ number_format($vendor->balance ?? 0, 2) }}</option>`;
                    @endforeach
                @endif
                
                let banksHtml = '';
                @if(isset($banks) && $banks->count() > 0)
                    @foreach ($banks as $bank)
                        @php 
                            $bankBalance = $bank->account_balance ?? $bank->balance ?? 0; 
                        @endphp
                        banksHtml += `<option value="bank_{{ $bank->id }}" data-type="bank" data-name="{{ $bank->name }}" data-balance="{{ $bankBalance }}" ${accountValue == 'bank_{{ $bank->id }}' ? 'selected' : ''}>🏦 {{ $bank->name }} (Bank) - Balance: PKR {{ number_format($bankBalance, 2) }}</option>`;
                    @endforeach
                @endif
                
                let cashHtml = '';
                @if(isset($cash) && $cash)
                    cashHtml += `<option value="cash_{{ $cash->id }}" data-type="cash" data-name="Cash Account" data-balance="{{ $cash->balance ?? 0 }}" ${accountValue == 'cash_{{ $cash->id }}' ? 'selected' : ''}>💰 Cash Account (Cash) - Balance: PKR {{ number_format($cash->balance ?? 0, 2) }}</option>`;
                @endif
                
                let expensesHtml = '';
                @if(isset($expenses) && $expenses->count() > 0)
                    @foreach ($expenses as $expense)
                        expensesHtml += `<option value="expense_{{ $expense->id }}" data-type="expense" data-name="{{ $expense->name }}" data-balance="0" ${accountValue == 'expense_{{ $expense->id }}' ? 'selected' : ''}>📄 {{ $expense->name }} (Expense)</option>`;
                    @endforeach
                @endif
                
                return `
                    <div class="journal-entry-row" data-row-id="${rowId}">
                        <div class="entry-number">Entry ${parseInt(rowId) + 1}</div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-search me-1"></i> Account</label>
                                <select class="form-select account-select" name="account_ids[]" data-row="${rowId}" style="width: 100%;">
                                    <option value="">🔍 Search by name...</option>
                                    <optgroup label="CUSTOMERS">${customersHtml}</optgroup>
                                    <optgroup label="VENDORS">${vendorsHtml}</optgroup>
                                    <optgroup label="BANKS">${banksHtml}</optgroup>
                                    <optgroup label="CASH">${cashHtml}</optgroup>
                                    <optgroup label="EXPENSES">${expensesHtml}</optgroup>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-success"><i class="fas fa-arrow-up me-1"></i> Credit (Money In)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent">PKR</span>
                                    <input type="number" class="form-control amount-input credit-amount" name="credit_amounts[]" step="0.01" min="0" placeholder="0.00" value="${creditValue}" data-row="${rowId}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-danger"><i class="fas fa-arrow-down me-1"></i> Debit (Money Out)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent">PKR</span>
                                    <input type="number" class="form-control amount-input debit-amount" name="debit_amounts[]" step="0.01" min="0" placeholder="0.00" value="${debitValue}" data-row="${rowId}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-align-left me-1"></i> Description</label>
                                <input type="text" class="form-control" name="descriptions[]" placeholder="Optional description..." value="${descriptionValue.replace(/"/g, '&quot;')}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger remove-row-btn" data-row="${rowId}" ${isFirst ? 'style="display: none;"' : ''}>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Load existing entry data
            function loadExistingEntry() {
                let existingData = [];
                @if($entry)
                    // Create an array with the existing entry data
                    let debitAmount = {{ $entry->amount ?? 0 }};
                    let isDebit = {{ $entry->debit_type && $entry->debit_id ? 'true' : 'false' }};
                    let isCredit = {{ $entry->credit_type && $entry->credit_id ? 'true' : 'false' }};
                    let accountType = '{{ $entry->debit_type ?? $entry->credit_type ?? '' }}';
                    let accountId = {{ $entry->debit_id ?? $entry->credit_id ?? 0 }};
                    
                    let accountValue = '';
                    if (isDebit) {
                        accountValue = accountType + '_' + accountId;
                    } else if (isCredit) {
                        accountValue = accountType + '_' + accountId;
                    }
                    
                    existingData.push({
                        account: accountValue,
                        debit: isDebit ? debitAmount : 0,
                        credit: isCredit ? debitAmount : 0,
                        description: '{{ $entry->description ?? '' }}'
                    });
                @endif
                
                return existingData;
            }

            function saveCurrentData() {
                rowData = [];
                $('.journal-entry-row').each(function(index) {
                    let row = $(this);
                    rowData.push({
                        account: row.find('.account-select').val(),
                        debit: row.find('.debit-amount').val(),
                        credit: row.find('.credit-amount').val(),
                        description: row.find('input[name="descriptions[]"]').val()
                    });
                });
            }

            function renderRows(initialData = null) {
                let container = $('#journalEntriesContainer');
                container.empty();
                
                let dataToRender = initialData || rowData || [];
                
                if (dataToRender.length === 0) {
                    // Create a default empty row if no data
                    dataToRender = [{ account: '', debit: 0, credit: 0, description: '' }];
                }
                
                dataToRender.forEach((data, index) => {
                    let rowHtml = createNewRow(index, index === 0, data);
                    container.append(rowHtml);
                });
                
                rowCounter = dataToRender.length - 1;
                
                $('.account-select').each(function() { initSelect2(this); });
                calculateTotals();
            }

            $('#addRowBtn').click(function(e) {
                e.preventDefault();
                saveCurrentData();
                rowCounter++;
                let newRowHtml = createNewRow(rowCounter, false, null);
                $('#journalEntriesContainer').append(newRowHtml);
                initSelect2($(`.journal-entry-row[data-row-id="${rowCounter}"] .account-select`));
                $('.journal-entry-row').each(function(index) {
                    $(this).find('.entry-number').text(`Entry ${index + 1}`);
                    $(this).attr('data-row-id', index);
                });
                calculateTotals();
            });

            $(document).on('click', '.remove-row-btn', function(e) {
                e.preventDefault();
                let rowId = $(this).data('row');
                $(`.journal-entry-row[data-row-id="${rowId}"]`).remove();
                $('.journal-entry-row').each(function(index) {
                    $(this).find('.entry-number').text(`Entry ${index + 1}`);
                    $(this).attr('data-row-id', index);
                });
                rowCounter = $('.journal-entry-row').length - 1;
                calculateTotals();
            });

            $(document).on('input', '.debit-amount, .credit-amount', function() {
                let row = $(this).closest('.journal-entry-row');
                let debitVal = parseFloat(row.find('.debit-amount').val()) || 0;
                let creditVal = parseFloat(row.find('.credit-amount').val()) || 0;
                
                // Prevent both debit and credit in same row
                if ($(this).hasClass('debit-amount') && debitVal > 0) {
                    row.find('.credit-amount').val(0);
                }
                if ($(this).hasClass('credit-amount') && creditVal > 0) {
                    row.find('.debit-amount').val(0);
                }
                
                calculateTotals();
            });

            function calculateTotals() {
                let totalDebit = 0, totalCredit = 0;
                $('.debit-amount').each(function() { totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-amount').each(function() { totalCredit += parseFloat($(this).val()) || 0; });
                
                $('#totalDebitDisplay').text('PKR ' + totalDebit.toLocaleString('en-US', {minimumFractionDigits: 2}));
                $('#totalCreditDisplay').text('PKR ' + totalCredit.toLocaleString('en-US', {minimumFractionDigits: 2}));
                
                let difference = totalDebit - totalCredit;
                let isBalanced = Math.abs(difference) < 0.01;
                
                if (isBalanced) {
                    $('#totalDifferenceDisplay').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i> Balanced: PKR 0.00</span>');
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#totalDifferenceDisplay').html('<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Difference: PKR ' + Math.abs(difference).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span>');
                    $('#submitBtn').prop('disabled', true);
                }
            }

            // Initialize with existing data
            let initialData = loadExistingEntry();
            renderRows(initialData);
        });
    </script>
@endsection