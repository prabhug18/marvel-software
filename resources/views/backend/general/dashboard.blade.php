@extends('layouts.backend')

@section('content')
<!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;">

            {{-- <div class="dropdown-area row g-3 mb-4">
                <div class="col-md-6">
                    <select class="form-select select2 border border-warning" id="categorySelect">
                        <option value="">Select Category</option>
                        <option value="Laptops">Laptops</option>
                        <option value="Computers">Computers</option>
                        <option value="Mobiles">Mobiles</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <select class="form-select select2 border border-warning" id="brandSelect">
                        <option value="">Select Brand</option>
                        <option value="Hp">Hp</option>
                        <option value="Asus">Asus</option>
                        <option value="Lenova">Lenova</option>
                    </select>
                </div>
            </div> --}}

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card gradient-1">
                        <div class="icon"><i class="bi bi-list"></i></div>
                        <h6>Category</h6>
                        <h2>{{ $categoryCount }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card gradient-2">
                        <div class="icon"><i class="bi bi-box"></i></div>
                        <h6>Stock</h6>
                        <h2>{{ $stockCount }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card gradient-3">
                        <div class="icon"><i class="bi bi-tag"></i></div>
                        <h6>Brand</h6>
                        <h2>{{ $brandCount }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card gradient-4">
                        <div class="icon"><i class="bi bi-people"></i></div>
                        <h6>Customer</h6>
                        <h2>{{ $customerCount }}</h2>
                    </div>
                </div>
            </div>
            
        </div>


    </main>

    <script>
        $(document).ready(function() {
            $('#brandFilter').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter1').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter2').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter3').select2({
                placeholder: "Select Category",
                allowClear: true
            });
        });
    </script>

@endsection