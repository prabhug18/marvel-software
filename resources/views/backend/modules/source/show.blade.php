@extends('layouts.backend')
@section('content')
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')
        <div class="container-fluid px-3">
            <div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
                <div class="card p-4 shadow-lg border-0" style="max-width: 420px; width: 100%;">
                    <div class="d-flex flex-column align-items-center mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-database fa-2x text-primary"></i>
                        </div>
                        <h2 class="fw-bold mb-1 text-center">{{ $source->name }}</h2>
                        <span class="badge {{ $source->status ? 'bg-success' : 'bg-danger' }} px-3 py-2 mb-2">{{ $source->status ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="text-muted small">Created At</div>
                            <div class="fw-semibold">{{ $source->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Updated At</div>
                            <div class="fw-semibold">{{ $source->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <a href="{{ route('source.index') }}" class="btn btn-primary w-100 mt-2"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
                </div>
            </div>
                </div>
            </div>
        </div>
    </main>
@endsection
