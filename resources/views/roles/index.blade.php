@extends('layouts.backend')

@section('content')
<div class="toggle-btn" id="toggleBtn">
    <i class="fas fa-bars"></i>
</div>

@include('backend.include.mnubar')

<main class="main-content" id="mainContent">
    @include('backend.include.header')

    @if(session('success'))
        <div class="alert alert-success" role="alert"> 
            {{ session('success') }}
        </div>
    @endif

    <div class="container-fluid px-3">
        <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col text-end mb-3">
                        @can('role-create')
                        <a class="btn custom-orange-btn text-white" href="{{ route('roles.create') }}">
                            <i class="fas fa-user-plus me-2"></i>Create New Role
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive" id="responsive-table">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="custom-thead text-center">
                            <tr>
                                <th>S.No</th>
                                <th>Name</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $key => $role)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        {{-- <a class="btn btn-info btn-sm" href="{{ route('roles.show',$role->id) }}"><i class="fa-solid fa-list"></i></a> --}}
                                        @can('role-edit')
                                            <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('roles.edit',$role->id) }}"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('role-delete')
                                        <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="btn" >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $roles->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>
</main>
@endsection