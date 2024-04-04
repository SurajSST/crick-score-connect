@extends('layouts.admin')
@section('title', isset($match) ? 'Edit Match' : 'Create Match')
@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($match) ? 'Edit Match' : 'Create Match' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($match) ? route('admin.matches.update', $match->id) : route('admin.matches.store') }}" method="POST">
                    @csrf
                    @if(isset($match))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Title</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="title" value="{{ old('title', isset($match) ? $match->title : '') }}" placeholder="Title">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Type</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="type" value="{{ old('type', isset($match) ? $match->type : '') }}" placeholder="type">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Sport</label>
                        <div class="col-sm-9">
                            <select name="sport_id" class="form-control form-control-lg default-select">
                                @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ old('sport_id', isset($match) && $match->sport_id == $sport->id ? 'selected' : '') }}>
                                    {{ $sport->name }}
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
                                <option value="{{ $team->id }}" {{ old('team1_id', isset($match) && $match->team1_id == $team->id ? 'selected' : '') }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Team 2</label>
                        <div class="col-sm-9">
                            <select name="team2_id" class="form-control form-control-lg default-select">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team2_id', isset($match) && $match->team2_id == $team->id ? 'selected' : '') }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" class="form-control" name="date" value="{{ old('date', isset($match) ? date('Y-m-d\TH:i', strtotime($match->date)) : '') }}" placeholder="Match Date and Time">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Venue</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="venue" value="{{ old('venue', isset($match) ? $match->venue : '') }}" placeholder="Venue">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <select name="status" class="form-control form-control-lg default-select">
                                <option value="live" {{ old('status', isset($match) && $match->status == 'live' ? 'selected' : '') }}>Live</option>
                                <option value="upcoming" {{ old('status', isset($match) && $match->status == 'upcoming' ? 'selected' : '') }}>Upcoming</option>
                                <option value="ended" {{ old('status', isset($match) && $match->status == 'ended' ? 'selected' : '') }}>Ended</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="3" placeholder="Description">{{ old('description', isset($match) ? $match->description : '') }}</textarea>
                        </div>
                    </div>

                    <!-- Add other fields related to Match -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($match) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
