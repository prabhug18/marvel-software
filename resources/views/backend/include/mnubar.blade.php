<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" />
    </div>
    <ul>
        <li><a href="{{ url('/') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li>
            <a href="{{ url('/customer') }}">
                <i class="fa-solid fa-user"></i><span>Customers</span>
            </a>
        </li> 
        
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-warehouse"></i><span>Stock</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ url('/stocks') }}">
                        <i class="fa-solid fa-list"></i><span> Stock List</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/stocks/create') }}">
                        <i class="fas fa-plus-circle"></i><span>Add Stock</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('stock.export') }}">
                        <i class="fas fa-plus-circle"></i><span>Bulk Stock</span>
                    </a>
                </li>                
            </ul>
        </li>
    
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-boxes-stacked"></i><span>Products</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ url('/products') }}">
                        <i class="fas fa-list"></i><span> Products List</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/products/create') }}">
                        <i class="fas fa-plus-circle"></i><span> Add Products</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('export.product') }}">
                        <i class="fas fa-box-open"></i><span> Bulk Products</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-warehouse"></i><span>Locations</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ url('/locations/create') }}">
                        <i class="fas fa-plus-circle"></i><span> Add Locations</span>
                    </a>
                </li>                
            </ul>
        </li>

        <li><a href="./invoice/invoice-list.html"><i class="fa-solid fa-file-invoice"></i><span>Invoices</span></a></li>
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fa-solid fa-hand-holding-dollar"></i><span>Price</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li>
                    <a href="./price/Add-price.html">
                        <i class="fas fa-plus-circle"></i><span> Add price</span>
                    </a>
                </li>
                <li>
                    <a href="./price/bulk-price.html">
                        <i class="fas fa-plus-circle"></i><span>Bulk Price</span>
                    </a>
                </li>                
            </ul>
        </li>
    
        <li><a href="./logs/logs.html"><i class="fas fa-clipboard-list"></i><span>All Logs</span></a></li>

        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-sitemap"></i><span>Masters</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{ url('/brands/create') }}">
                        <i class="fas fa-tags"></i><span>Brands</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/categories/create') }}">
                        <i class="fas fa-th-large"></i><span>Categories</span>
                    </a>
                </li>
            </ul>
        </li>
    
    </ul>
</nav>