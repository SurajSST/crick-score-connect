@extends('layouts.admin')
@section('title', isset($player) ? 'Edit Player' : 'Create Player')
@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($player) ? 'Edit Player' : 'Create Player' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($player) ? route('admin.players.update', $player->id) : route('admin.players.store') }}" method="POST">
                    @csrf
                    @if(isset($player))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Team</label>
                        <div class="col-sm-9">
                            <select name="team_id" class="form-control form-control-lg default-select">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id', isset($player) && $player->team_id == $team->id ? 'selected' : '') }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Player Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name', isset($player) ? $player->name : '') }}" placeholder="Player Name">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Position</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="position" value="{{ old('position', isset($player) ? $player->position : '') }}" placeholder="Position">
                        </div>
                    </div>

                    <!-- Add other fields related to Player -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($player) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
