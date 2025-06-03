@extends('layouts.backend')

@section('content')

<div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        @session('success')
            <div class="alert alert-success" role="alert"> 
                {{ $value }}
            </div>
        @endsession

        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                <div class="row">
                    <div class="col text-end mb-3">
                        <a class="btn custom-orange-btn text-white" href="{{ route('users.create') }}">
                            <i class="fas fa-user-plus me-2"></i>Create New User
                        </a>
                    </div>                
                </div>
          
                <div class="table-responsive" id="responsive-table">
                    <table id="customerTable" class="table table-bordered table-hover align-middle">
                    <thead class="custom-thead text-center">
                            <tr>
                                <th>S.No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example rows -->
                            
                            @foreach ($data as $key => $user)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                    @if(!empty($user->getRoleNames()))
                                        @foreach($user->getRoleNames() as $v)
                                        <label class="badge bg-success">{{ $v }}</label>
                                        @endforeach
                                    @endif
                                    </td>
                                    <td>
                                        {{-- <a class="btn btn-info btn-sm" href="{{ route('users.show',$user->id) }}"><i class="fa-solid fa-list"></i></a> --}}
                                        <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('users.edit',$user->id) }}"><i class="fas fa-edit"></i></a>
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="btn" >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach    
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



@endsection