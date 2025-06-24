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
                @if(Session::has('create_brand'))
                    <div class="alert alert-success col-md-12">
                        <strong>{{session('create_brand')}}</strong>
                    </div>
                @endif
                @if(Session::has('delete_brand'))
                    <div class="alert alert-danger col-md-12">
                        <strong>{{session('delete_brand')}}</strong>
                    </div>
                @endif
                @if(Session::has('edit_brand'))
                    <div class="alert alert-warning col-md-12">
                        <strong>{{session('edit_brand')}}</strong>
                    </div>
                @endif
            </div>
              <form class="row g-4" method="POST" enctype="multipart/form-data" action="{{ route('brands.store') }}">
                  @csrf
                  <div class="col-md-6">               
                    <input type="text" name="name" placeholder="Enter brand name" class="form-control" style="flex: 1;" required>
                  </div>
                  <div class="col-md-4">                    
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
                @foreach($brand as $brandVal)
                  <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $brandVal->name }}</td>                            
                    <td class="action-buttons">                  
                      <a class="btn btn-sm btn-outline-primary me-1"  href="{{ route('brands.edit',$brandVal->id) }}"  style="text-decoration: none;"><i class="fas fa-edit"></i></a>                  
                      @php
                        $hasProducts = \App\Models\Product::where('brand_id', $brandVal->id)->exists();
                      @endphp
                      <form method="POST" action="{{ route('brands.destroy', $brandVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
                        @csrf
                        @method('DELETE')
                        @if($hasProducts)
                          <span data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot delete: Brand used in products">
                            <button type="submit" class="btn btn-sm btn-outline-danger" disabled style="pointer-events: none; opacity: 0.6;"><i class="fas fa-trash-alt"></i></button>
                          </span>
                        @else
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                        @endif
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
                        // Enable Bootstrap 5 tooltips for dynamically rendered buttons
                        function initTooltips() {
                          document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                            new bootstrap.Tooltip(el);
                          });
                        }
                        document.addEventListener('DOMContentLoaded', function () {
                          setTimeout(initTooltips, 500);
                        });
                        // Also re-initialize tooltips after AJAX or pagination if needed
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
        
    </main>
   
@endsection