@extends('layouts.admin')

@push('styles')
<link href="{{ asset('backend/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Live Match Comments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Match Title</th>
                                <th>User Name</th>
                                <th>Live Link</th>
                                <th>Comment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through live match comment data -->
                            @foreach($comments as $key => $comment)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $comment->liveMatch->match->title }}</td>
                                <td>{{ $comment->user->name }}</td>
                                <td>{{ $comment->live_link }}</td>
                                <td>{{ $comment->comment }}</td>
                                <td>
                                    <div class="d-flex">
                                        <!-- <a href="{{ route('admin.live-match-comment.edit', $comment->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a> -->

                                        <form action="{{ route('admin.live-match-comment.destroy', $comment->id) }}" method="POST" style="display: inline;">
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
