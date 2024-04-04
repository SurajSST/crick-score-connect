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
                        <label class="col-sm-3 col-form-label">Country</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="country" value="{{ old('country', isset($user) ? $user->country : '') }}" placeholder="User country">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" name="email" value="{{ old('email', isset($user) ? $user->email : '') }}" placeholder="User Email">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Role</label>
                        <div class="col-sm-9">
                            <select class="form-control form-control-lg default-select" name="role">
                                <option value="admin" {{ isset($user) && $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="user" {{ isset($user) && $user->role == 'user' ? 'selected' : '' }}>User</option>
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
