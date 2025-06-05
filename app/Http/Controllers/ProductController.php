<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\GST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\ProductExport;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $product    =    Product::orderBy('updated_at', 'desc')->get();
        $heading    =   "Product View";
        return view('backend.modules.products.index', compact('heading','product'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $product    =    Product::orderBy('updated_at', 'desc')->get();
        $heading    =   "Add New Product";
        $gstRates   =   GST::all();
        return view('backend.modules.products.create', compact('heading','product','gstRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'model' => 'required|string',
            'brand_id' => 'required|string',
            'series' => 'required|string',
            'processor' => 'required|string',
            'memory' => 'required|string',
            'operating_system' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'hsn_code' => 'required|string',
            'product_images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the combination of category_id, brand_id, and model already exists
        $existingProduct = Product::where('category_id', $request->input('category_id'))
            ->where('brand_id', $request->input('brand_id'))
            ->where('model', $request->input('model'))
            ->first();

        if ($existingProduct) {
            return response()->json([
                'errors' => ['model' => ['The combination of category, brand, and model already exists.']]
            ], 422);
        }

        if($request->file('product_images')){
            $image              =   $request->file('product_images');
            $fileNameOriginal   =   $_FILES['product_images']['name'];
            $fileName           =   time().'.'.$image->getClientOriginalExtension();
            $destinationPath    =   public_path('/assets/uploads');
            $image->move($destinationPath, $fileName);
        }else{
            $fileName           =   '';
            $fileNameOriginal   =   '';
        }

        $id = auth()->user()->id;

        $product = new Product([
            'category_id'               =>  $request->input('category_id'),
            'brand_id'                  =>  $request->input('brand_id'),
            'model'                     =>  $request->input('model'),
            'series'                    =>  $request->input('series'),           
            'processor'                 =>  $request->input('processor'),
            'memory'                    =>  $request->input('memory'),
            'operating_system'          =>  $request->input('operating_system'),
            'price'                     =>  $request->input('price'),
            'tax_percentage'            =>  $request->input('tax_percentage'),
            'hsn_code'                  =>  $request->input('hsn_code'),
            'product_images'            =>  $fileName,
            'product_images_original'   =>  $fileNameOriginal,            
            'user_id'                   =>  auth()->user()->id
        ]);

        $product->save();

        return response()->json(['message' => 'Product created successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }
        // If there is a matching stock, do NOT allow editing
        $hasStock = \App\Models\Stock::where('model', $product->model)
            ->where('category_id', $product->category_id)
            ->where('brand_id', $product->brand_id)
            ->exists();
        if ($hasStock) {
            return redirect()->route('products.index')->with('error', 'Cannot edit: Product has stock in location.');
        }
        $heading = "Edit Product";
        $category = Category::pluck('name','id');
        $brand = Brand::pluck('name','id');
        $gstRates = GST::all();
        return view('backend.modules.products.edit',compact('product','heading','category','brand','gstRates'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'model' => 'required|string',
            'brand_id' => 'required|string',
            'series' => 'required|string',
            'processor' => 'required|string',
            'memory' => 'required|string',
            'operating_system' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'hsn_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if($request->file('product_images')){
            $image              =   $request->file('product_images');
            $fileNameOriginal   =   $_FILES['product_images']['name'];
            $fileName           =   time().'.'.$image->getClientOriginalExtension();
            $destinationPath    =   public_path('/assets/uploads');
            $image->move($destinationPath, $fileName);
        }else{
            $fileName           =   '';
            $fileNameOriginal   =   '';
        }        
        $id                                 =   $request->input('id');

        $product                            =   Product::find($id);
        $product->category_id               =   $request->input('category_id');
        $product->brand_id                  =   $request->input('brand_id');
        $product->model                     =   $request->input('model');
        $product->series                    =   $request->input('series');
        $product->processor                 =   $request->input('processor');
        $product->memory                    =   $request->input('memory');
        $product->operating_system          =   $request->input('operating_system');
        $product->price                     =   $request->input('price');
        $product->tax_percentage            =   $request->input('tax_percentage');
        $product->hsn_code                  =   $request->input('hsn_code');
        $product->product_images            =   $fileName;   
        $product->product_images_original   =   $fileNameOriginal;
        $product->user_id                   =   auth()->user()->id;
        $product->save();

        return response()->json(['message' => $id. ' Product updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //    
        $product = Product::findOrFail($id);
        $product->delete();
        Session::flash('delete_product','Product deleted successfully');
        return redirect('products');
    }

    public function exportPage()
    {
        $heading    =   "Product Excel Export";        
        return view('backend.modules.products.bulkExport', compact('heading'));
    }

    /**
     * Export products to Excel
     */
    public function export() 
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }

    /**
     * Import products from Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            Excel::import(new ProductImport, $request->file('file'));

            return response()->json(['message' => 'Products imported successfully!']);
        } catch (\Exception $e) {
            \Log::error('Product import failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during import. Please check the file and try again.'], 500);
        }
    }

    /**
     * AJAX: Search products for auto-suggestion in invoice
     */
    public function search(Request $request)
    {
        $q = $request->input('q');
        $warehouseId = $request->input('warehouse_id');
        $products = Product::with(['brand', 'category'])
            ->where(function($query) use ($q) {
                $query->where('model', 'like', "%$q%")
                    ->orWhere('series', 'like', "%$q%")
                    ->orWhereHas('brand', function($query) use ($q) {
                        $query->where('name', 'like', "%$q%") ;
                    })
                    ->orWhereHas('category', function($query) use ($q) {
                        $query->where('name', 'like', "%$q%") ;
                    });
            })
            ->limit(10)
            ->get();
        $result = $products->map(function($p) use ($warehouseId) {
            // Get stock for this product (by model, category, brand, and warehouse if provided)
            $stockQuery = \App\Models\Stock::where('model', $p->model)
                ->where('category_id', $p->category_id)
                ->where('brand_id', $p->brand_id);
            if ($warehouseId) {
                $stockQuery->where('warehouse_id', $warehouseId);
            }
            $stock = $stockQuery->sum('qty');
            return [
                'brand' => $p->brand ? $p->brand->name : '',
                'series' => $p->series,
                'model' => $p->model,
                'category' => $p->category ? $p->category->name : '',
                'price' => $p->price,
                'tax_percentage' => $p->tax_percentage,
                'stock' => $stock,
            ];
        });
        return response()->json($result);
    }

    public function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }
}
