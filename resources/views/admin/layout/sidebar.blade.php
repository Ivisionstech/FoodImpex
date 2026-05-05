<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- Check if global company settings and logo exist --}}
                @if (isset($companySettings) && $companySettings->logo)
                    <img src="{{ asset('storage/' . $companySettings->logo) }}"
                        style="max-width: 180px; max-height: 50px; object-fit: contain;"
                        alt="{{ $companySettings->name ?? 'Company Logo' }}" class="img-fluid">
                @else
                    {{-- Fallback to default logo if no data in database --}}
                    <img src="{{ asset('images/logo.png') }}" width="200px"
                        alt="Default Logo" class="img-fluid">
                @endif
            </span>
        </a>

        {{-- Sidebar Toggle Button --}}
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">

        <!-- Dashboard: Sabko dikhega -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Banks - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('banks.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M11.5 1L2 6v2h19V6m-5 4v7h3v-7M2 22h19v-3H2m8-9v7h3v-7m-9 0v7h3v-7z" />
                </svg>
                <div style="padding-left: 13px" data-i18n="Customers">Banks</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('banks.list') ? 'active' : '' }}">
                    <a href="{{ route('banks.list') }}" class="menu-link">
                        <div data-i18n="List">All Banks</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Cash Amount - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('cash.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wallet"></i>
                <div data-i18n="Customers">Cash Amount</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('cash.list') ? 'active' : '' }}">
                    <a href="{{ route('cash.list') }}" class="menu-link">
                        <div data-i18n="List">View Cash</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Purchase / Vendors - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('vendors.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div data-i18n="Vendors">Purchase</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('vendors.list') ? 'active' : '' }}">
                    <a href="{{ route('vendors.list') }}" class="menu-link">
                        <div>All Purchasers</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('vendors.bills.list') ? 'active' : '' }}">
                    <a href="{{ route('vendors.bills.list') }}" class="menu-link">
                        <div>All Bills</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('vendors.payments.list') ? 'active' : '' }}">
                    <a href="{{ route('vendors.payments.list') }}" class="menu-link">
                        <div>Send Payments</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Sales / Customers - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('customers.*') || request()->routeIs('bills.*') || request()->routeIs('payments.*') || request()->routeIs('quotations.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Customers">Sales</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('customers.list') ? 'active' : '' }}">
                    <a href="{{ route('customers.list') }}" class="menu-link">
                        <div>All Customer</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('bills.list') ? 'active' : '' }}">
                    <a href="{{ route('bills.list') }}" class="menu-link">
                        <div>All Invoices</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('quotations.list') ? 'active' : '' }}">
                    <a href="{{ route('quotations.list') }}" class="menu-link">
                        <div>Quotations</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('customers.receive-payment.list') ? 'active' : '' }}">
                    <a href="{{ route('customers.receive-payment.list') }}" class="menu-link">
                        <div>Received Payment</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Stock / Products - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('products.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div data-i18n="Products">Products</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('products.list') ? 'active' : '' }}">
                    <a href="{{ route('products.list') }}" class="menu-link">
                        <div>All Products</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('products.create') ? 'active' : '' }}">
                    <a href="{{ route('products.create') }}" class="menu-link">
                        <div>Add Product</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Finances - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('profits.*') || request()->routeIs('expenses.*') || request()->routeIs('daybooks.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div>Finances</div>
            </a>
            <ul class="menu-sub">
                <!-- <li class="menu-item {{ request()->routeIs('profits.list') ? 'active' : '' }}">
                    <a href="{{ route('profits.list') }}" class="menu-link">
                        <div>Profits</div>
                    </a>
                </li> -->

                <li class="menu-item {{ request()->routeIs('expenses.list') ? 'active' : '' }}">
                    <a href="{{ route('expenses.list') }}" class="menu-link">
                        <div>Expenses</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('daybooks.list') ? 'active' : '' }}">
                    <a href="{{ route('daybooks.list') }}" class="menu-link">
                        <div>Daybook</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- General Transactions - Only General Entry and Entries History -->
        <li class="menu-item {{ request()->routeIs('general-transactions.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-transfer-alt"></i>
                <div data-i18n="General Transactions">General Transactions</div>
            </a>
            <ul class="menu-sub">
                <!-- General Entry - Active -->
                <li class="menu-item {{ request()->routeIs('general-transactions.general-entry') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.general-entry') }}" class="menu-link">
                        <div>General Entry</div>
                    </a>
                </li>

                <!-- Entries History - Active -->
                <li class="menu-item {{ request()->routeIs('general-transactions.entries-list') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.entries-list') }}" class="menu-link">
                        <div>Entries History</div>
                    </a>
                </li>

                {{-- COMMENTED OUT PAGES - KEEP FOR FUTURE USE --}}
                {{--
                <li class="menu-item {{ request()->routeIs('general-transactions.index') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.index') }}" class="menu-link">
                        <div>Overview</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('general-transactions.customer-to-vendor') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.customer-to-vendor') }}" class="menu-link">
                        <div>Customer to Vendor</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('general-transactions.bank-to-bank') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.bank-to-bank') }}" class="menu-link">
                        <div>Bank to Bank</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('general-transactions.bank-withdraw') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.bank-withdraw') }}" class="menu-link">
                        <div>Bank Withdraw</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('general-transactions.bank-deposit') ? 'active' : '' }}">
                    <a href="{{ route('general-transactions.bank-deposit') }}" class="menu-link">
                        <div>Bank Deposit</div>
                    </a>
                </li>
                --}}
            </ul>
        </li>

        <!-- Access Control - Only Admin can see (Hidden from Accountant) -->
        @admin
            <li class="menu-item {{ request()->routeIs('access-control.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                    <div>Access Control</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('access-control.roles.*') ? 'active' : '' }}">
                        <a href="{{ route('access-control.roles.index') }}" class="menu-link">
                            <div>Roles</div>
                        </a>
                    </li>

                    <li class="menu-item {{ request()->routeIs('access-control.permissions.*') ? 'active' : '' }}">
                        <a href="{{ route('access-control.permissions.index') }}" class="menu-link">
                            <div>Users & Permissions</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endadmin

        <!-- Company Information - Both Admin and Accountant can see -->
        <li class="menu-item {{ request()->routeIs('company.*') ? 'active' : '' }}">
            <a href="{{ route('company.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-buildings"></i>
                <div data-i18n="CompanyInfo">Company Information</div>
            </a>
        </li>

        <!-- Logout -->
        <li class="menu-item">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div data-i18n="Customers">Logout</div>
            </a>
        </li>
    </ul>
</aside>