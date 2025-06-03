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
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $warehouseVal->name }}</td>
                                    <td>{{ $warehouseVal->prefix }}</td>              
                                    <td class="text-center">                  
                                    <a href="{{ route('locations.edit',$warehouseVal->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>                  
                                    <form method="POST" action="{{ route('locations.destroy', $warehouseVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
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