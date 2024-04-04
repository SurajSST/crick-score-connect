@extends('layouts.admin')

@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($liveMatch) ? 'Edit Live Match' : 'Create Live Match' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($liveMatch) ? route('admin.live-match.update', $liveMatch->id) : route('admin.live-match.store') }}" method="POST">
                    @csrf
                    @if(isset($liveMatch))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Match Title</label>
                        <div class="col-sm-9">
                            <select name="match_id" class="form-control form-control-lg default-select">
                                @foreach($matches as $match)
                                <option value="{{ $match->id }}" {{ old('match_id', isset($liveMatch) ? $liveMatch->match_id : '') == $match->id ? 'selected' : '' }}>
                                    {{ $match->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Live Link</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="live_link" value="{{ old('live_link', isset($liveMatch) ? $liveMatch->live_link : '') }}" placeholder="Live Link">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Comment</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="comment" placeholder="Comment">{{ old('comment', isset($liveMatch) ? $liveMatch->comment : '') }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($liveMatch) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
