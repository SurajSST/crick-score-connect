@extends('layouts.admin')
@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($team) ? 'Edit Team' : 'Create Team' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($team) ? route('admin.teams.update', $team->id) : route('admin.teams.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($team))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Sport</label>
                        <div class="col-sm-9">
                            <select name="sport_id" class="form-control form-control-lg default-select">
                                @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ old('sport_id', isset($team) && $team->sport_id == $sport->id ? 'selected' : '') }}>
                                    {{ $sport->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Team Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name', isset($team) ? $team->name : '') }}" placeholder="Team Name">
                        </div>
                    </div>

                    <!-- Add file input for the logo -->
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Logo</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="logo" name="logo">
                                    <label class="custom-file-label" for="logo">Choose file</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add other fields related to Team -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($team) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
