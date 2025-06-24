@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
          
        <div style="padding-top: 30px;"></div>
            <div class="container-fluid px-3">
                <div class="card shadow-sm rounded-4 mt-4">
                    <div class="card-body">

                        <div class="col-md-6">
                            @include('backend.include.formError')
                            @if(Session::has('create_warehouse'))
                                <div class="alert alert-success col-md-12">
                                    <strong>{{session('create_warehouse')}}</strong>
                                </div>
                            @endif
                           
                            @if(Session::has('edit_warehouse'))
                                <div class="alert alert-warning col-md-12">
                                    <strong>{{session('edit_warehouse')}}</strong>
                                </div>
                            @endif
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="{{ route('locations.store') }}">
                            @csrf
                            <div id="warehouseStockFields">
                                <div class="row g-3 align-items-end warehouse-entry mb-3">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" placeholder="Enter Location" name="name[]" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" placeholder="Enter Invoice Prefix" name="prefix[]" required>
                                    </div>
                                    <div class="col-md-2 d-flex gap-2">
                                        <button type="button" class="btn btn-success" onclick="addField()"><i class="fas fa-plus"></i></button>
                                        <button type="button" class="btn btn-danger" onclick="onclick="this.closest('.warehouse-entry').remove()"">-</button>
                                    </div>
                                </div>
                            </div>                                    
                            <div class="col-2">
                                <button type="submit" class="btn btn-success btn-lg">Submit</button>
                            </div>
                        </form>
                            
                    </div>  
                </div>
            </div>
        </div>

        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <div class="col-6">
                        @if(Session::has('delete_success'))
                        <div class="alert alert-danger col-md-12">
                            <strong>{{session('delete_success')}}</strong>
                        </div>
                        @endif
                    </div>
                    
                    
                    <!-- Responsive Table -->
                    <div class="table-responsive mt-5">
                        <table id="customerTable" class="table table-striped table-bordered align-middle">

                            <thead class="custom-thead text-center">
                            <tr>
                                <th scope="col">S.No</th>
                                <th scope="col">Location</th>
                                <th scope="col">Prefix</th>            
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                <!-- Example rows -->
                                <?php $i    =   1; ?>
                                @foreach($warehouse as $warehouseVal)
                                @php
                                    $hasInvoices = \App\Models\Invoice::where('warehouse_id', $warehouseVal->id)->exists();
                                @endphp
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $warehouseVal->name }}</td>
                                    <td>{{ $warehouseVal->prefix }}</td>
                                    <td class="action-buttons">
                                        <a href="{{ route('locations.edit',$warehouseVal->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                        <form method="POST" action="{{ route('locations.destroy', $warehouseVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
                                            @csrf
                                            @method('DELETE')
                                            @if($hasInvoices)
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot delete: Location used in invoice">
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
    display: flex;
    flex-direction: row;
    align-items: flex-start;
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
    min-width: 110px;
    flex-shrink: 0;
    content: attr(data-label);
    display: inline-block;
    font-weight: bold;
    color: #f47820;
    margin-bottom: 0;
    margin-right: 0.5rem;
    font-size: 0.98em;
    white-space: pre-line;
    word-break: break-word;
    text-align: left;
    padding-right: 0.7rem;
  }
  #customerTable td > *:not(:first-child) {
    margin-left: auto;
    text-align: right;
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
    <script>
        // function addField() {
        //     const container = document.getElementById('warehouseStockFields');
        //     const newRow = document.createElement('div');
        //     newRow.classList.add('row', 'warehouse-entry');
        
        //     newRow.innerHTML = `
        //     <input type="text" class="stock-input" placeholder="Enter Warehouse" name="name[]" id="name[]" required>
        //     <input type="text" class="stock-input" placeholder="Enter Invoice Prefix" name="prefix[]" id="prefix[]" required>
        //     <button type="button" class="warehouse-add-btn" onclick="addField()"><i class="fas fa-plus"></i></button>
        //         <button type="button" class="warehouse-remove-btn" onclick="this.parentElement.remove()">-</button>
        //     `;
        //     container.appendChild(newRow);
        // }

        function addField() {
            const container = document.getElementById('warehouseStockFields');
            const existingEntry = document.querySelector('.warehouse-entry');
            const newEntry = existingEntry.cloneNode(true);

            // Clear the inputs in the cloned node
            newEntry.querySelectorAll('input').forEach(input => input.value = '');

            // Re-attach event listener to remove button
            const removeBtn = newEntry.querySelector('.btn-danger');
            removeBtn.onclick = function () {
            this.closest('.warehouse-entry').remove();
            };

            container.appendChild(newEntry);
        }
        
    </script>
@endsection