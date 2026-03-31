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
    public function index(Request $request)
    {
        $heading = "Product View";
        $products = Product::withCount('invoices')->orderBy('updated_at', 'desc')->get();
        if ($request->ajax()) {
            return view('backend.modules.products.partials.product_rows', compact('products'))->render();
        }
        return view('backend.modules.products.index', compact('heading', 'products'));
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
            'model_no' => 'nullable|string',
            'brand_id' => 'required|string',
            'series' => 'required|string',
            'specification' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'hsn_code' => 'required|string',
            'product_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'offer_price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|string',
            'remarks' => 'nullable|string',
            'foc_months' => 'nullable|string',
            'prorata_months' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate (Category + Brand + Model + Model No)
        $exists = Product::where([
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'model' => $request->model,
            'model_no' => $request->model_no,
        ])->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['model' => ['This exact product already exists.']]
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
            'model_no'                  =>  $request->input('model_no'),
            'series'                    =>  $request->input('series'),           
            'specification'             =>  $request->input('specification'),
            'price'                     =>  $request->input('price'),
            'tax_percentage'            =>  $request->input('tax_percentage'),
            'hsn_code'                  =>  $request->input('hsn_code'),
            'product_images'            =>  $fileName,
            'product_images_original'   =>  $fileNameOriginal,            
            'user_id'                   =>  auth()->user()->id,
            'offer_price'                =>  $request->input('offer_price'),
            'capacity'                  =>  $request->input('capacity'),
            'remarks'                   =>  $request->input('remarks'),
            'foc_months'                =>  $request->input('foc_months'),
            'prorata_months'            =>  $request->input('prorata_months'),
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
            'model_no' => 'nullable|string',
            'brand_id' => 'required|string',
            'series' => 'required|string',
            'specification' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'hsn_code' => 'required|string',
            'product_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'offer_price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|string',
            'remarks' => 'nullable|string',
            'foc_months' => 'nullable|string',
            'prorata_months' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate (Category + Brand + Model + Model No) excluding current product ID
        $product = Product::findOrFail($id);
        
        $hasChanged = (
            $product->category_id != $request->category_id ||
            $product->brand_id != $request->brand_id ||
            $product->model != $request->model ||
            $product->model_no != $request->model_no
        );

        if ($hasChanged) {
            $exists = Product::where([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'model' => $request->model,
                'model_no' => $request->model_no,
            ])->where('id', '!=', $id)->exists();

            if ($exists) {
                return response()->json([
                    'errors' => ['model' => ['This combination already exists in another product record. Please use unique details.']]
                ], 422);
            }
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

        $product->category_id               =   $request->input('category_id');
        $product->brand_id                  =   $request->input('brand_id');
        $product->model                     =   $request->input('model');
        $product->model_no                  =   $request->input('model_no');
        $product->series                    =   $request->input('series');
        $product->specification             =   $request->input('specification');
        $product->price                     =   $request->input('price');
        $product->tax_percentage            =   $request->input('tax_percentage');
        $product->hsn_code                  =   $request->input('hsn_code');
        $product->product_images            =   $fileName;   
        $product->product_images_original   =   $fileNameOriginal;
        $product->user_id                   =   auth()->user()->id;
        $product->offer_price               =   $request->input('offer_price');
        $product->capacity                  =   $request->input('capacity');
        $product->remarks                   =   $request->input('remarks');
        $product->foc_months                =   $request->input('foc_months');
        $product->prorata_months            =   $request->input('prorata_months');
        $product->save();

        return response()->json(['message' => $product->id. ' Product updated successfully!']);
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
                'file' => 'required'
            ]);

            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductImport, $request->file('file'));

            return response()->json(['message' => 'Products imported successfully!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors as JSON with 422 status
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Product import failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during import. Please check the file and try again.'], 500);
        }
    }

    /**
     * AJAX: Preview Product Import
     */
    public function importPreview(Request $request)
    {
        try {
            $request->validate(['file' => 'required|mimes:xlsx,xls']);
            $file = $request->file('file');
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\ProductImport, $file);

            if (empty($data) || empty($data[0])) {
                return response()->json(['error' => 'Excel file is empty.'], 422);
            }

            $rows = $data[0];
            $headers = array_shift($rows); // Remove header row
            
            $previewData = [];
            foreach ($rows as $index => $row) {
                // If row is mostly empty, skip it
                if (count(array_filter($row)) < 3) continue;

                $categoryName = trim($row[1] ?? '');
                $brandName = trim($row[2] ?? '');
                $model = trim($row[3] ?? '');
                $taxPercentage = is_numeric($row[7] ?? null) ? floatval($row[7]) : null;

                $category = \App\Models\Category::where('name', $categoryName)->first();
                $brand = \App\Models\Brand::where('name', $brandName)->first();
                
                // Fetch valid GST rates once for performance or just check exists
                $validTax = false;
                if ($taxPercentage !== null) {
                    $validTax = \App\Models\GST::where('name', 'like', $taxPercentage . '%')->exists();
                }

                $isValid = ($category && $brand && !empty($model) && $validTax);
                $statusMsg = '';
                if (!$category) $statusMsg .= "Category '$categoryName' not found. ";
                if (!$brand) $statusMsg .= "Brand '$brandName' not found. ";
                if (empty($model)) $statusMsg .= "Model is empty. ";
                if (!$validTax) $statusMsg .= "Tax rate '$taxPercentage%' not found in masters. ";

                $previewData[] = [
                    'category' => $categoryName,
                    'brand' => $brandName,
                    'model' => $model,
                    'model_no' => $row[4] ?? '',
                    'warranty' => $row[5] ?? '',
                    'tax_percentage' => $taxPercentage,
                    'price' => $row[8] ?? '',
                    'is_valid' => $isValid,
                    'status_msg' => trim($statusMsg)
                ];

                // Limit preview to 50 rows for performance
                if (count($previewData) >= 50) break;
            }

            return response()->json([
                'preview' => $previewData,
                'total_rows' => count($rows)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
                    ->orWhere('model_no', 'like', "%$q%")
                    ->orWhere('series', 'like', "%$q%")
                    ->orWhereHas('brand', function($query) use ($q) {
                        $query->where('name', 'like', "%$q%") ;
                    })
                    ->orWhereHas('category', function($query) use ($q) {
                        $query->where('name', 'like', "%$q%") ;
                    });
            })
            ->limit(20)
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
                'id' => $p->id,
                'brand' => $p->brand ? $p->brand->name : '',
                'series' => $p->series,
                'model' => $p->model,
                'model_no' => $p->model_no,
                'category' => $p->category ? $p->category->name : '',
                'price' => $p->price,
                'offer_price' => $p->offer_price,
                'tax_percentage' => $p->tax_percentage,
                'stock' => $stock,
            ];
        })->values();
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
