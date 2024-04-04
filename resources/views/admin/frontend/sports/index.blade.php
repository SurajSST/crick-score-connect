@extends('layouts.admin')
@section('title', 'Sports)
@push('styles')
<link href="{{asset('backend/vendor/datatables/css/jquery.dataTables.min.css')}}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Sports DataTable</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through sports data -->
                            @foreach($sports as $key => $sport)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $sport->name }}</td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.sports.edit', $sport->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>

                                        <form action="{{ route('admin.sports.destroy', $sport->id) }}" method="POST" style="display: inline;">
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
<script src="{{asset('backend/vendor/datatables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/js/plugins-init/datatables.init.js')}}"></script>
@endpush
