<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

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
        return view('backend.modules.products.create', compact('heading','product'));
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
            'product_images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Add rules for warehouse[] and stock[] if needed
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
            'product_images'            =>  $fileName,
            'product_images_original'   =>  $fileNameOriginal,            
            'user_id'                   =>  auth()->user()->id
        ]);

        $product->save();

        // Save product logic here (e.g., Product::create([...]))
        // Handle file uploads if needed

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
        //
        $product    =   Product::find($id);
        $heading    =   "Edit Product";
        $category   =   Category::pluck('name','id');
        $brand      =   Brand::pluck('name','id');
        return view('backend.modules.products.edit',compact('product','heading','category','brand'));
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
}
