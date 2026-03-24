@extends('layouts.backend')

@section('content')
@include('backend.include.mnubar')
<main class="main-content" id="mainContent">
    @include('backend.include.header')
    <div class="container-fluid px-3" style="padding-top:30px;">
        <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <h4>Invoice Template</h4>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.settings.invoice_template.update') }}">
                    @csrf
                    <div class="mb-3 col-lg-4">
                        <label class="form-label">Select Invoice Template</label>
                        <select name="invoice_template" class="form-select" required>
                            @foreach($available as $key => $label)
                                <option value="{{ $key }}" @if($current == $key) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-lg-4">
                        <label class="form-label">Head Office Location (Format Four)</label>
                        <select name="head_office_warehouse_id" class="form-select">
                            <option value="">-- Use invoice location (default) --</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" @if(isset($currentHead) && $currentHead == $w->id) selected @endif>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
