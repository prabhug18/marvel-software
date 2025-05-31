<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WarehouseController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:warehouse-list|warehouse-create|warehouse-edit|warehouse-delete', ['only' => ['index','store']]);
         $this->middleware('permission:warehouse-create', ['only' => ['create','store']]);
         $this->middleware('permission:warehouse-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:warehouse-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $warehouse      =    Warehouse::orderBy('updated_at', 'desc')->get();
        $heading        =   "Add New Locations";
        return view('backend.modules.locations.create', compact('heading','warehouse'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $warehouse      =    Warehouse::orderBy('updated_at', 'desc')->get();
        $heading        =   "Add New Location";
        return view('backend.modules.locations.create', compact('heading','warehouse'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'name'      => 'required|array|min:1',            
            'prefix'    => 'required|array|min:1'
        ]);

        $id = auth()->user()->id;

        $input      =   $request->all();
        $name       =   $request->name;
        $prefix     =   $request->prefix;

        $countValues    =   count($name);

        $arrayValues    =   [];
        for ($x = 0; $x < $countValues; $x++) {
            $arrayValues[$x]['name']    =   $name[$x];
            $arrayValues[$x]['prefix']  =   $prefix[$x];
        }

        foreach($arrayValues as $arrayValuesVal){            
            $warehouse              =   new Warehouse();
            $warehouse->name        =   $arrayValuesVal['name'];
            $warehouse->prefix      =   $arrayValuesVal['prefix'];
            $warehouse->user_id     =   $id;
            $warehouse->save();
        }    

        Session::flash('create_warehouse','Location created successfully');

        return redirect('locations');        
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $warehouse  =   Warehouse::find($id);
        $heading    =   "Edit Location";
        return view('backend.modules.locations.edit',compact('warehouse','heading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'name'      => 'required',            
            'prefix'    => 'required'
        ]);
    
        $input      =   $request->all();
        $warehouse  =   Warehouse::find($id);
        $warehouse->update($input);
           
        Session::flash('edit_warehouse','Location edited successfully');

        return redirect('locations');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        Session::flash('delete_success','Location deleted successfully');
        return redirect()->route('locations.create');
    }
}
