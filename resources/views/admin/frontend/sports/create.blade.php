@extends('layouts.admin')
@section('title', isset($sport) ? 'Edit Sport' : 'Create Sport')
@section('body')
<div class="col-xl-6 col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($sport) ? 'Edit Sport' : 'Create Sport' }}</h4>
        </div>
        <div class="card-body">
            <div class="basic-form">
                <form action="{{ isset($sport) ? route('admin.sports.update', $sport->id) : route('admin.sports.store') }}" method="POST">
                    @csrf
                    @if(isset($sport))
                    @method('PUT')
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name', isset($sport) ? $sport->name : '') }}" placeholder="Sport Name">
                        </div>
                    </div>

                    <!-- Add other fields related to Sport -->

                    <div class="form-group row">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">{{ isset($sport) ? 'Update' : 'Create' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
