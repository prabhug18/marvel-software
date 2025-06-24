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
                      <th>S.No</th>
                      <th>Name</th>                      
                      <th>Mobile No</th>
                      <th>City</th>                            
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <!-- Example rows -->
                  
                  <?php $i    =   1; ?>
                  @foreach($customer as $customerVal)
                    <tr>
                      <td><span class="mobile-value">{{ $i }}</span></td>
                      <td><span class="mobile-value">{{ $customerVal->name }}</span></td>                     
                      <td><span class="mobile-value">{{ $customerVal->mobile_no }}</span></td> 
                      <td><span class="mobile-value">{{ $customerVal->city->name }}</span></td>                                  
                      <td class="action-buttons">                  
                        <span class="mobile-value">
                          <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('customer.edit',$customerVal->id) }}"  style="text-decoration: none;"><i class="fas fa-edit"></i></a>                  
                          @if($customerVal->invoices_count == 0)
                            <form method="POST" action="{{ route('customer.destroy', $customerVal->id) }}" class="btn"  onsubmit="return ConfirmDelete()">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                            </form>
                          @endif
                        </span>
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
    <style>
@media (max-width: 767.98px) {
  .table-responsive {
    font-size: 0.95rem;
  }
  #customerTable, #customerTable thead, #customerTable tbody, #customerTable th, #customerTable tr {
    display: block;
    width: 100%;
  }
  #customerTable thead {
    display: none;
  }
  #customerTable tr {
    margin-bottom: 1.2rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    background: #fff;
    padding: 0.5rem;
  }
  #customerTable td {
    display: block;
    padding: 0.5rem 0.5rem 0.5rem 0.5rem;
    border: none;
    border-bottom: 1px solid #eee;
    position: relative;
    min-height: 40px;
    word-break: break-word;
    white-space: normal;
    background: #fff;
  }
  #customerTable td:before {
    content: attr(data-label);
    display: block;
    font-weight: bold;
    color: #f47820;
    margin-bottom: 0.2rem;
    font-size: 0.98em;
    white-space: pre-line;
    word-break: break-word;
  }
  #customerTable td .mobile-value {
    margin-left: 100px;
    display: inline-block;
  }
  #customerTable td:last-child {
    border-bottom: none;
  }
  .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-start;
  }
}
</style>
<script>
function setCustomerTableDataLabels() {
    var headers = Array.from(document.querySelectorAll('#customerTable thead th')).map(th => th.innerText.trim());
    document.querySelectorAll('#customerTable tbody tr').forEach(function(row) {
        row.querySelectorAll('td').forEach(function(td, i) {
            td.setAttribute('data-label', headers[i] || '');
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    setCustomerTableDataLabels();
});
</script>
@endsection