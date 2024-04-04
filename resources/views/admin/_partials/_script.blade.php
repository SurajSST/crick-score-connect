<!-- Required vendors -->
<script src="{{ asset('backend/vendor/global/global.min.js') }}"></script>
<script src="{{ asset('backend/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('backend/vendor/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/custom.min.js') }}"></script>
<script src="{{ asset('backend/js/deznav-init.js') }}"></script>
<script src="{{ asset('backend/vendor/bootstrap-datetimepicker/js/moment.js') }}"></script>
<script src="{{ asset('backend/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>

<!-- Chart piety plugin files -->
<script src="{{ asset('backend/vendor/peity/jquery.peity.min.js') }}"></script>
<!-- Apex Chart -->
<!-- <script src="{{ asset('backend/vendor/apexchart/apexchart.js') }}"></script> -->

<!-- Dashboard 1 -->
<script src="{{ asset('backend/js/dashboard/dashboard-1.js') }}"></script>
@stack('scripts')
<script>
    $(function() {
        $('#datetimepicker1').datetimepicker({
            inline: true,
        });
    });
</script>

