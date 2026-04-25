<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Food Impex</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.min.css">
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" /> --}}
 
    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Custom CSS to fix navbar z-index for SweetAlert2 -->
    <style>
       .layout-navbar, .layout-navbar.navbar-detached, .navbar {
            z-index: 1000 !important;
        }
 /* 1. Force the length menu to stay in a single horizontal line */
.dt-length label {
    display: inline-flex !important; /* Forces items to stay together */
    align-items: center !important;
    white-space: nowrap !important;  /* CRITICAL: Prevents text from dropping below */
    gap: 10px !important;            /* Adds nice space between box and text */
    color: #697a8d;
    font-size: 0.9rem;
    margin-left: 10px !important;
}

/* 2. Style the Select Box and ensure the arrow is visible */
.dt-length select {
    display: inline-block !important;
    width: 80px !important; /* Fixed width so it doesn't grow too large */
    padding: 0.4375rem 1.8rem 0.4375rem 0.75rem !important;
    border: 1px solid #d9dee3 !important;
    border-radius: 0.375rem !important;
    background-color: #fff !important;
    
    /* Custom Arrow SVG */
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.6rem center !important;
    background-size: 12px 12px !important;
    
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
}

/* 3. Give the whole top row some breathing room from the edges */
.dt-layout-row {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    padding: 15px 25px !important; /* Pushes content away from the container edges */
}

/* 4. Fix Search Box alignment on the right */
.dt-search {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.dt-search input {
    border: 1px solid #d9dee3 !important;
    border-radius: 0.375rem !important;
    padding: 0.4375rem 0.875rem !important;
}
    


    
    </style>
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        @include('admin.layout.sidebar')
        <div class="layout-page">
        @include('admin.layout.navbar')
          <div class="content-wrapper">
          @yield('content')
          @include('admin.layout.footer')
            <div class="content-backdrop fade"></div>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>


    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/2.3.6/js/dataTables.min.js"></script>
    <script>
        $(".ajax-form").on("submit", function(event) {
            event.preventDefault();
            let form = $(this);
            form.find('.invalid-feedback').text('');
            form.find('.input-error-msg').text('');


            let submitBtn = form.find("#submitButton");
            let originalText = submitBtn.text();
            let formData = new FormData(this);
            let url = form.attr('action');

            submitBtn.prop("disabled", true).html('<span style="color: white;"></span>Processing ... ');

            $.ajax({
                url: url,
                data: formData,
                type: "POST",
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === true) {
                        let modal = form.closest(".modal");
                        if (modal.length) {
                            modal.modal('hide');
                        }
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message || 'Saved Successfully.',
                            showConfirmButton: false,
                            timer: 2500
                        });

                        setTimeout(function() {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                location.reload();
                            }
                        }, 2500);
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.message || 'Internal server error',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                },
                error: function(response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;

                        $.each(errors, function(field, messages) {
                            $('#' + field + '-error').text(messages[0]);
                        });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Internal server error',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop("disabled", false).html(originalText);
                }
            });
        });
        $('.action-confirm1').on('click', function(e) {
            e.preventDefault();

            let button = $(this);
            let url = button.data('url');
            let text = button.data('text');
            let buttonText = button.data('button-text');
            let cancelButtonTest = button.data('cancel-button-text') || 'Cancel';
            let successMessage = button.data('success-message') || 'Saved Successfully.';

            // Ask for secret pin first
            Swal.fire({
                title: 'Enter Secret PIN',
                input: 'password',
                inputPlaceholder: 'Enter your secret PIN',
                inputAttributes: {
                    maxlength: 4,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Verify',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You must enter the PIN!';
                    }
                }
            }).then((pinResult) => {
                if (pinResult.isConfirmed) {
                    if (pinResult.value === '0000') {
                        // PIN correct → show delete confirmation
                        Swal.fire({
                            title: "Are you sure?",
                            text: text,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: buttonText,
                            cancelButtonText: cancelButtonTest,
                            showLoaderOnConfirm: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr(
                                            'content'),
                                    },
                                    success: function(response) {
                                        if (response.status === true) {
                                            Swal.fire({
                                                toast: true,
                                                position: 'top-end',
                                                icon: 'success',
                                                title: response.message ||
                                                    successMessage,
                                                showConfirmButton: false,
                                                timer: 2500
                                            });
                                            setTimeout(function() {
                                                location.reload();
                                            }, 2500);
                                        } else {
                                            Swal.fire({
                                                toast: true,
                                                position: 'top-end',
                                                icon: 'error',
                                                title: response.message ||
                                                    'Internal server error',
                                                showConfirmButton: false,
                                                timer: 2500
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            toast: true,
                                            position: 'top-end',
                                            icon: 'error',
                                            title: 'Internal server error',
                                            showConfirmButton: false,
                                            timer: 2500
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        // Wrong PIN
                        Swal.fire({
                            icon: 'error',
                            title: 'Wrong PIN!',
                            text: 'You entered an incorrect secret key.',
                            confirmButtonColor: '#d33',
                        });
                    }
                }
            });
        });

        $('.action-confirm').on('click', function() {
            let button = $(this);
            let url = button.data('url');
            let text = button.data('text');
            let buttonText = button.data('button-text');
            let cancelButtonTest = button.data('cancel-button-text') || 'Cancel';

            let successMessage = button.data('success-message') || 'Saved Successfully.';

            Swal.fire({
                title: "Are you sure?",
                text: text,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: buttonText,
                cancelButtonText: cancelButtonTest,
                showLoaderOnConfirm: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message || successMessage,
                                    showConfirmButton: false,
                                    timer: 2500
                                });
                                setTimeout(function() {
                                    location.reload();
                                }, 2500);
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: response.message || 'Internal server error',
                                    showConfirmButton: false,
                                    timer: 2500
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Internal server error',
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        let table = new DataTable('.table');
    </script>
 @if (Session::has('message'))
<script>
    var status = {{ json_encode(Session::get('status')) }};
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: status === 'true' ? 'success' : 'error',
        title: "{{ Session::get('message') }}",
        showConfirmButton: false,
        timer: 4000
    });
     if (window.$.fn.DataTable) {
        window.$.fn.DataTable = undefined;
    }
    if (window.DataTable) {
        window.DataTable = undefined;
    }


     $(document).ready(function() {
        // Completely disable DataTables on bills list page
        if (window.location.href.indexOf('bills/list') !== -1) {
            if (typeof $.fn.DataTable !== 'undefined') {
                $.fn.DataTable = undefined;
            }
            // Remove any DataTable instances
            $('table').removeClass('dataTable');
        }


    });



    $(document).ready(function() {
    $('.table').not('#billsTable').DataTable();
});








</script>
@endif
    @stack('scripts')

  </body>
</html>
