<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CategoryController extends Controller
{
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

        $id = auth()->user()->id;

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
        //
        Category::where('id', $id)->delete();
        Session::flash('delete_category','Category deleted successfully');
        return redirect('categories');
    }
}
