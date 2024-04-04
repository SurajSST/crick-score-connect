@extends('layouts.admin')

@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($highlight) ? 'Edit Highlight' : 'Create Highlight' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($highlight) ? route('admin.highlights.update', $highlight->id) : route('admin.highlights.store') }}" method="POST">
                    @csrf
                    @if(isset($highlight))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Match Title</label>
                        <div class="col-sm-9">
                            <select name="match_id" class="form-control form-control-lg default-select">
                                @foreach($matches as $match)
                                <option value="{{ $match->id }}" {{ old('match_id', isset($highlight) ? $highlight->match_id : '') == $match->id ? 'selected' : '' }}>
                                    {{ $match->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Highlight Link</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="highlight_link" value="{{ old('highlight_link', isset($highlight) ? $highlight->highlight_link : '') }}" placeholder="Highlight Link">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($highlight) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
