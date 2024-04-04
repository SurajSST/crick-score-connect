<!-- ... other meta tags ... -->
<title>@yield('title')</title>
<!-- Favicon icon -->
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('backend/images/favicon.png') }}">
<link href="{{ asset('backend/vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('backend/vendor/chartist/css/chartist.min.css') }}">
<link href="{{ asset('backend/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
<!-- Add SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('sweetalert.js') }}"></script>>
@stack('styles')
<link href="{{ asset('backend/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

