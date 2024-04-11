@extends('layouts.admin')
@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($user) ? 'Edit User' : 'Create User' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($user) ? route('admin.user.update', $user->id) : route('admin.user.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($user))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name', isset($user) ? $user->name : '') }}" placeholder="User Name">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="username" value="{{ old('username', isset($user) ? $user->username : '') }}" placeholder="Username">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" name="email" value="{{ old('email', isset($user) ? $user->email : '') }}" placeholder="User Email">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Date of Birth</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="dob" value="{{ old('dob', isset($user) ? $user->dob : '') }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Phone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', isset($user) ? $user->phone : '') }}" placeholder="User Phone">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Address</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="address" value="{{ old('address', isset($user) ? $user->address : '') }}" placeholder="User Address">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Player Type</label>
                        <div class="col-sm-9">
                            <select class="form-control form-control-lg default-select" name="playerType">
                                <option value="Bowler" {{ isset($user) && $user->playerType == 'Bowler' ? 'selected' : '' }}>Bowler</option>
                                <option value="Batsman" {{ isset($user) && $user->playerType == 'Batsman' ? 'selected' : '' }}>Batsman</option>
                                <option value="Wicket-keeper" {{ isset($user) && $user->playerType == 'Wicket-keeper' ? 'selected' : '' }}>Wicket-keeper</option>
                                <option value="All-Rounder" {{ isset($user) && $user->playerType == 'All-Rounder' ? 'selected' : '' }}>All-Rounder</option>
                            </select>
                        </div>
                    </div>

                    <!-- Add other fields related to User -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
