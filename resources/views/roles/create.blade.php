@extends('layouts.backend')

@section('content')
<div class="toggle-btn" id="toggleBtn">
    <i class="fas fa-bars"></i>
</div>
@include('backend.include.mnubar')
<main class="main-content" id="mainContent">
    @include('backend.include.header')
    <div class="container-fluid px-3">
        <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="mb-0">Create New Role</h4>
                    </div>
                    <div class="col-md-3 text-end float-end">
                        <a class="btn btn-primary btn-sm" href="{{ route('roles.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('roles.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label"><strong>Name</strong></label>
                                <input type="text" name="name" placeholder="Name" class="form-control" value="{{ old('name') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label"><strong>Manage Permissions</strong></label>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="30%">Module</th>
                                                <th class="text-center">Create</th>
                                                <th class="text-center">Read (List)</th>
                                                <th class="text-center">Update (Edit)</th>
                                                <th class="text-center">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modules as $moduleName => $actions)
                                                <tr>
                                                    <td class="fw-bold text-capitalize">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input select-all-row" id="row-{{ $moduleName }}">
                                                            <label class="form-check-label ms-1" for="row-{{ $moduleName }}">
                                                                {{ str_replace('_', ' ', $moduleName) }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    @foreach(['create', 'list', 'edit', 'delete'] as $actionKey)
                                                        <td class="text-center">
                                                            @if(isset($actions[$actionKey]))
                                                                <input type="checkbox" name="permission[]" value="{{ $actions[$actionKey]->id }}" 
                                                                       class="form-check-input permission-check">
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 mb-3">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Create Role
                            </button>
                        </div>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Select All Row functionality
                        const selectAllRowChecks = document.querySelectorAll('.select-all-row');
                        selectAllRowChecks.forEach(rowCheck => {
                            rowCheck.addEventListener('change', function() {
                                const checkboxes = this.closest('tr').querySelectorAll('.permission-check');
                                checkboxes.forEach(cb => {
                                    cb.checked = this.checked;
                                });
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</main>
@endsection