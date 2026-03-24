@extends('layouts.backend')

@section('content')
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        <div class="container-fluid mt-3">
            <div class="row justify-content-center">
                <div class="col-10">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Edit Terms & Conditions</h5>
                            <a href="{{ route('terms.index') }}" class="btn btn-sm btn-light">Back to list</a>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('terms.update', $term->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <label class="form-label">Content</label>
                                            <textarea name="content" rows="12" class="form-control" placeholder="Enter the terms and conditions">{{ old('content', $term->content ?? '') }}</textarea>
                                            <small class="form-text text-muted">This content will appear on invoices. Keep formatting plain and concise.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border-0">
                                            <div class="card-body p-0">
                                                <!-- default term removed -->

                                                <div class="mb-3 form-check">
                                                    <input type="hidden" name="active" value="0">
                                                    <input type="checkbox" name="active" value="1" class="form-check-input" id="active" {{ old('active', $term->active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="active">Active</label>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-primary">Update</button>
                                                    <a href="{{ route('terms.index') }}" class="btn btn-secondary">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
