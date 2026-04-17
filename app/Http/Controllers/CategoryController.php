<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:category-list|category-create|category-edit|category-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:category-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:category-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $category   =    Category::get();
        $heading    =   "Category View";
        return view('backend.modules.category.create', compact('heading','category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $category   =    Category::get();
        $heading    =   "Category View";
        return view('backend.modules.category.create', compact('heading','category'));
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

        $id = auth()->id(); // Fixed undefined method 'user'

        $category               =   new Category();
        $category->name         =   $request->name;        
        $category->user_id      =   $id;
        $category->save();

        Session::flash('create_category','Category created successfully');

        return redirect('categories');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category) // Corrected type from 'Brand' to 'Category'
    {
        //
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $category      =   Category::find($id);
        $heading    =   "Edit Categories";
        return view('backend.modules.category.edit',compact('category','heading'));
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
    
        $input          =   $request->all();
        $category       =   Category::find($id);
        $category->update($input);
           
        Session::flash('edit_category','Category edited successfully');

        return redirect('categories');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Check if category is used in products or stocks
        $productCount = Product::where('category_id', $id)->count();
        $stockCount = Stock::where('category_id', $id)->count();

        if ($productCount > 0 || $stockCount > 0) {
            Session::flash('error', 'Cannot delete Category: It is currently assigned to ' . $productCount . ' Products and ' . $stockCount . ' Stocks.');
            return redirect('categories');
        }

        Category::where('id', $id)->delete();
        Session::flash('delete_category', 'Category deleted successfully');
        return redirect('categories');
    }
}
