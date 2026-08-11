<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center w-100" id="navbar-collapse">
        <div class="d-flex align-items-center justify-content-between w-100 py-2">
            
            <div class="nav-stat-card d-flex flex-column align-items-center text-center flex-grow-1 border-end">
                <div class="stat-icon-wrapper mb-1">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                        <i class="bx bxs-bank fs-4"></i>
                    </span>
                </div>
                <small class="stat-label text-muted text-uppercase fw-bold">Bank Amount</small>
                <h6 class="mb-0 text-primary fw-bold">PKR {{ number_format($banks->sum('account_balance'), 2) }}</h6>
            </div>

            <div class="nav-stat-card d-flex flex-column align-items-center text-center flex-grow-1 border-end">
                <div class="stat-icon-wrapper mb-1">
                    <span class="avatar-initial rounded-circle bg-label-success">
                        <i class="bx bx-money fs-4"></i>
                    </span>
                </div>
                <small class="stat-label text-muted text-uppercase fw-bold">Cash Amount</small>
                <h6 class="mb-0 text-success fw-bold">PKR {{ $cash ? number_format($cash->balance, 0) : '0' }}</h6>
            </div>

            <div class="nav-stat-card d-flex flex-column align-items-center text-center flex-grow-1 border-end">
                <div class="stat-icon-wrapper mb-1">
                    <span class="avatar-initial rounded-circle bg-label-info">
                        <i class="bx bx-wallet fs-4"></i>
                    </span>
                </div>
                <small class="stat-label text-muted text-uppercase fw-bold">Total Amount</small>
                <h6 class="mb-0 text-info fw-bold">PKR {{ number_format($banks->sum('account_balance') + ($cash ? $cash->balance : 0), 2) }}</h6>
            </div>

            <div class="nav-stat-card d-flex flex-column align-items-center text-center flex-grow-1">
                <div class="stat-icon-wrapper mb-1">
                    <span class="avatar-initial rounded-circle bg-label-danger">
                        <i class="bx bx-trending-down fs-4"></i>
                    </span>
                </div>
                <small class="stat-label text-muted text-uppercase fw-bold">Total Expense</small>
                <h6 class="mb-0 text-danger fw-bold">PKR {{ number_format($totalExpenses, 2) }}</h6>
            </div>

        </div>
    </div>
</nav>

<style>
    /* Main Stat Card Styling */
    .nav-stat-card {
        transition: all 0.3s ease;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 8px;
    }

    /* Hover Effect: Lifts up slightly and adds background */
    .nav-stat-card:hover {
        transform: translateY(-3px);
        background-color: rgba(67, 89, 113, 0.04);
    }

    /* Label Styling */
    .stat-label {
        font-size: 0.65rem;
        letter-spacing: 0.8px;
        margin-bottom: 2px;
    }

    /* Vertical Divider Logic */
    .border-end {
        border-right: 1px solid rgba(67, 89, 113, 0.1) !important;
    }

    /* Navbar Height Adjustment */
    #layout-navbar {
        height: 90px !important; /* Slightly taller for the vertical stack */
    }

    /* Icon Scale Effect */
    .nav-stat-card:hover .stat-icon-wrapper {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    /* Responsive fix: Stack on small screens if needed */
    @media (max-width: 768px) {
        .stat-label { font-size: 0.55rem; }
        h6 { font-size: 0.8rem; }
        .border-end { border: none !important; }
    }
</style>