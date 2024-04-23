@extends('layouts.admin')

@push('styles')
<link href="{{ asset('backend/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Payment Details</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>User ID</th>
                                <th>Match ID</th>
                                <th>Match Key</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through payments data -->
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->user->name }}</td>
                                <td>{{ $payment->match_id }}</td>
                                <td>{{ optional($payment->match)->key }}</td>
                                <td>{{ $payment->transaction_id }}</td>
                                <td>{{ 10 }}</td>
                                <td>{{ $payment->created_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/js/plugins-init/datatables.init.js') }}"></script>
@endpush
