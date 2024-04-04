@extends('layouts.admin')

@section('title', isset($score) ? 'Edit Score' : 'Create Score')

@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($score) ? 'Edit Score' : 'Create Score' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($score) ? route('admin.scores.update', $score->id) : route('admin.scores.store') }}" method="POST">
                    @csrf
                    @if(isset($score))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Match</label>
                        <div class="col-sm-9">
                            <select name="match_id" class="form-control form-control-lg default-select">
                                @foreach($matches as $match)
                                <option value="{{ $match->id }}" {{ old('match_id', isset($score) && $score->match_id == $match->id ? 'selected' : '') }}>
                                    {{ $match->sport->name }} - {{ $match->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Team 1</label>
                        <div class="col-sm-9">
                            <select name="team1_id" class="form-control form-control-lg default-select">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team1_id', isset($score) && $score->team1_id == $team->id ? 'selected' : '') }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Points 1</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="team1_points" value="{{ old('team1_points', isset($score) ? $score->team1_points : '') }}" placeholder="Points 1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Team 2</label>
                        <div class="col-sm-9">
                            <select name="team2_id" class="form-control form-control-lg default-select">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team2_id', isset($score) && $score->team2_id == $team->id ? 'selected' : '') }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Points 2</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="team2_points" value="{{ old('team2_points', isset($score) ? $score->team2_points : '') }}" placeholder="Points 2">
                        </div>
                    </div>

                    <!-- Add other fields related to Score -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($score) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
