@extends('layouts.admin')
@push('styles')
<link href="{{ asset('backend/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Teams DataTable</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Sport</th>
                                <th>Team Name</th>
                                <th>Logo</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through teams data -->
                            @foreach($teams as $key => $team)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $team->sport->name }}</td>
                                <td>{{ $team->name }}</td>
                                <td>
                                    @if($team->logo)
                                    <img src="{{ asset($team->logo) }}" alt="Team Logo" class="img-thumbnail" width="100">
                                    @else
                                    No Logo
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>

                                        <form action="{{ route('admin.teams.destroy', $team->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger shadow btn-xs sharp" onclick="displayConfirmationDialog('Are you sure you want to delete?', this.form)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
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
