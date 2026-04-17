<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:brand-list|brand-create|brand-edit|brand-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:brand-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:brand-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:brand-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $brand      =    Brand::get();
        $heading    =   "Brand View";
        return view('backend.modules.brand.create', compact('heading','brand'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $brand      =    Brand::get();
        $heading    =   "Brand View";
        return view('backend.modules.brand.create', compact('heading','brand'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'name'      => 'required'
        ]);

        $id = auth()->user()->id;

        $brand               =   new Brand();
        $brand->name         =   $request->name;        
        $brand->user_id      =   $id;
        $brand->save();

        Session::flash('create_brand','Brand created successfully');

        return redirect('brands');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $brand      =   Brand::find($id);
        $heading    =   "Edit Brand";
        return view('backend.modules.brand.edit',compact('brand','heading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'name'      => 'required',                        
        ]);
    
        $input      =   $request->all();
        $brand      =   Brand::find($id);
        $brand->update($input);
           
        Session::flash('edit_brand','Brand edited successfully');

        return redirect('brands');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Check if brand is used in products or stocks
        $productCount = Product::where('brand_id', $id)->count();
        $stockCount = Stock::where('brand_id', $id)->count();

        if ($productCount > 0 || $stockCount > 0) {
            Session::flash('error', 'Cannot delete Brand: It is currently assigned to ' . $productCount . ' Products and ' . $stockCount . ' Stocks.');
            return redirect('brands');
        }

        Brand::where('id', $id)->delete();
        Session::flash('delete_brand', 'Brand deleted successfully');
        return redirect('brands');
    }
}
