<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete', ['only' => ['index','store']]);
         $this->middleware('permission:customer-create', ['only' => ['create','store']]);
         $this->middleware('permission:customer-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $customer      =    Customer::orderBy('updated_at', 'desc')->get();
        $heading        =   "Customer View";
        return view('backend.modules.customer.index', compact('heading','customer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $customer       =   Customer::orderBy('updated_at', 'desc')->get();
        $state          =   State::where('country_id','101')->get();
        $heading        =   "Add New Customer";
        return view('backend.modules.customer.create', compact('heading','customer','state'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'name'      => 'required',            
            'email'     => 'required|email|unique:customers',
            'mobile_no' => 'required|min:10',
        ]);

        $id = auth()->user()->id;

        $customer               =   new Customer();
        $customer->name         =   $request->name;
        $customer->email        =   $request->email;
        $customer->mobile_no    =   $request->mobile_no;
        $customer->address      =   $request->address;
        $customer->state_id        =   $request->state;
        $customer->city_id         =   $request->city;
        $customer->pincode      =   $request->pincode;
        $customer->user_id      =   $id;
        $customer->gst_no       =   $request->gst_no;
        $customer->save();

        Session::flash('create_customer','Customer created successfully');

        return redirect('customer');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $customer   =   Customer::find($id);
        $state      =   State::where('country_id','101')->pluck('name','id');
        if($customer->state_id) {
            $city = City::where('state_id', $customer->state_id)->pluck('name', 'id');
        } else {
            $city = [];
        }
       
        $heading    =   "Edit Customer";
        return view('backend.modules.customer.edit',compact('customer','heading','state','city'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'      => 'required',            
            'email'     => 'required|email|unique:customers,email,'.$id,
            'mobile_no' => 'required|min:10',
        ]);
    
        $customer = Customer::find($id);
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->mobile_no = $request->mobile_no;
        $customer->address = $request->address;
        $customer->state_id = $request->state;
        $customer->city_id = $request->city;
        $customer->pincode = $request->pincode;
        $customer->gst_no = $request->gst_no;
        $customer->save();
           
        Session::flash('edit_customer','Customer edited successfully');

        return redirect('customer');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //        
        $customer = Customer::findOrFail($id);
        $customer->delete();
        Session::flash('delete_customer','Customer deleted successfully');
        return redirect('customer');
    }

    public function getCity(Request $request)
    {
        $city = City::where('state_id', $request->state_id)->get();
        return response()->json($city);
    }

    /**
     * AJAX: Search customers for auto-suggestion
     */
    public function search(Request $request)
    {
        $q = $request->input('q');
        $customers = Customer::where('name', 'like', "%$q%")
            ->orWhere('mobile_no', 'like', "%$q%")
            ->orWhere('email', 'like', "%$q%")
            ->limit(10)
            ->get();
        return response()->json($customers);
    }
}
