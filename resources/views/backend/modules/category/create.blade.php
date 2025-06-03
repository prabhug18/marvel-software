@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        <div class="container-fluid px-3">
          <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-body">

              <div class="col-md-6">
                  @include('backend.include.formError')
                  @if(Session::has('create_category'))
                      <div class="alert alert-success col-md-12">
                          <strong>{{session('create_category')}}</strong>
                      </div>
                  @endif
                  @if(Session::has('delete_category'))
                      <div class="alert alert-danger col-md-12">
                          <strong>{{session('delete_category')}}</strong>
                      </div>
                  @endif
                  @if(Session::has('edit_category'))
                      <div class="alert alert-warning col-md-12">
                          <strong>{{session('edit_category')}}</strong>
                      </div>
                  @endif
              </div>

              <form class="row g-4" method="POST" enctype="multipart/form-data" action="{{ route('categories.store') }}">
                  @csrf
                  <div class="col-md-6">                
                    <input type="text" name="name" placeholder="Enter category name" class="form-control" style="flex: 1;" required>
                  </div>
                  <div class="col-md-6">                    
                    <button type="submit" class="btn btn-success btn-lg"> Save</button>                
                  </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="container-fluid px-3">
          <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-body">
          
              <div class="table-responsive" id="responsive-table">
                <table id="customerTable" class="table table-bordered table-hover align-middle">
                  <thead class="custom-thead text-center">
                    <tr>
                      <th>S.No</th>
                      <th>Name</th>                 
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <!-- Example rows -->
                  <?php $i    =   1; ?>
                  @foreach($category as $categoryVal)
                    <tr>
                      <td>{{ $i }}</td>
                      <td>{{ $categoryVal->name }}</td>                            
                      <td class="action-buttons">                  
                        <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('categories.edit',$categoryVal->id) }}"  style="text-decoration: none;"><i class="fas fa-edit"></i></a>                  
                        <form method="POST" action="{{ route('categories.destroy', $categoryVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                        <script>
                          function ConfirmDelete()
                          {
                              var x = confirm("Are you sure you want to delete?");
                              if (x)
                                  return true;
                              else
                                  return false;
                          }
                      </script>
                      </td>
                    </tr>
                    <?php $i++; ?>
                  @endforeach    
                  </tbody>
                </table>
              </div>

            </div>
          </div>
        </div>

      </div>        
    </main>   
@endsection