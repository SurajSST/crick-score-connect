@extends('layouts.admin')

@section('title', 'Scores')

@push('styles')
<link href="{{ asset('backend/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Scores</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Match</th>
                                <th>Title</th>
                                <th>Team 1</th>
                                <th>Points 1</th>
                                <th>Team 2</th>
                                <th>Points 2</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through scores data -->
                            @foreach($scores as $key => $score)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $score->match->sport->name }}</td>
                                <td>{{ $score->match->title }}</td>
                                <td>{{ $score->team1->name }}</td>
                                <td>{{ $score->team1_points }}</td>
                                <td>{{ $score->team2->name }}</td>
                                <td>{{ $score->team2_points }}</td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.scores.edit', $score->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>

                                        <form action="{{ route('admin.scores.destroy', $score->id) }}" method="POST" style="display: inline;">
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
