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
              @if(Session::has('create_customer'))
                  <div class="alert alert-success col-md-12">
                      <strong>{{session('create_customer')}}</strong>
                  </div>
              @endif
              @if(Session::has('delete_customer'))
                  <div class="alert alert-danger col-md-12">
                      <strong>{{session('delete_customer')}}</strong>
                  </div>
              @endif
              @if(Session::has('edit_customer'))
                  <div class="alert alert-warning col-md-12">
                      <strong>{{session('edit_customer')}}</strong>
                  </div>
              @endif
          </div>
              
              <div class="row">
                <div class="col text-end mb-3">
                  <a class="btn custom-orange-btn text-white" href="{{ url('/customer/create') }}">
                    <i class="fas fa-user-plus me-2"></i>Add Customer
                  </a>
                </div>
              </div>
              <div class="table-responsive" id="responsive-table">
                <table id="customerTable" class="table table-bordered table-hover align-middle">
                  <thead class="custom-thead text-center">
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>  
                      <th>Mobile No</th>
                      <th>Address</th>
                      <th>State</th>
                      <th>City</th>
                      <th>Pincode</th>          
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <!-- Example rows -->
                  
                  <?php $i    =   1; ?>
                  @foreach($customer as $customerVal)
                    <tr>
                      <td>{{ $i }}</td>
                      <td>{{ $customerVal->name }}</td>
                      <td>{{ $customerVal->email }}</td>
                      <td>{{ $customerVal->mobile_no }}</td>
                      <td>{{ $customerVal->address }}</td>
                      <td>{{ $customerVal->state->name }}</td>
                      <td>{{ $customerVal->city->name }}</td>
                      <td>{{ $customerVal->pincode }}</td>              
                      <td class="action-buttons">                  
                        <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('customer.edit',$customerVal->id) }}"  style="text-decoration: none;"><i class="fas fa-edit"></i></a>                  
                        <form method="POST" action="{{ route('customer.destroy', $customerVal->id) }}" class="btn"  onsubmit="return ConfirmDelete()">
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
        
    </main>
   
@endsection