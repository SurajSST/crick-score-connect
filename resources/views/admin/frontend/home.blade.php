@extends('layouts.admin')
@section('body')

<!-- row -->
<div class="container-fluid">
    <div class="form-head d-flex align-items-center mb-sm-4 mb-3">
        <div class="mr-auto">
            <h2 class="text-black font-w600">Dashboard</h2>
            <p class="mb-0">Admin Dashboard</p>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="media align-items-center">
                        <div class="media-body mr-3">
                            <h2 class="fs-34 text-black font-w600">{{ $totalUsers }}</h2>
                            <span>Total Users</span>
                        </div>
                        <i class="fa fa-users fa-3x"></i> <!-- Font Awesome users icon -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="media align-items-center">
                        <div class="media-body mr-3">
                            <h2 class="fs-34 text-black font-w600">{{ $totalTeams }}</h2>
                            <span>Total Teams</span>
                        </div>
                        <i class="fa fa-users fa-3x"></i> <!-- Font Awesome teams icon -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="media align-items-center">
                        <div class="media-body mr-3">
                            <h2 class="fs-34 text-black font-w600">{{ $totalMatches }}</h2>
                            <span>Total Matches</span>
                        </div>
                        <i class="fa fa-television fa-3x"></i> <!-- Font Awesome matches icon -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
