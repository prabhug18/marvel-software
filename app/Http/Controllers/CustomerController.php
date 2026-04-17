<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\State;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Use withCount to eager load invoice count for each customer
        $customer = Customer::withCount('invoices')->orderBy('updated_at', 'desc')->get();
        $heading = "Customer View";
        return view('backend.modules.customer.index', compact('heading','customer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer       =   Customer::orderBy('updated_at', 'desc')->get();
        $state          =   State::where('country_id','101')->get();
        $sources        =   Source::where('status', 1)->orderBy('name')->get();
        $heading        =   "Add New Customer";
        return view('backend.modules.customer.create', compact('heading','customer','state','sources'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'name'      => 'required',            
            'email'     => 'nullable|email',
            'mobile_no' => 'required|digits:10',
            'alternative_no' => 'nullable|digits:10',
            'address'   => 'nullable|string|max:255',
            'state'     => 'required|exists:states,id',
            'city'      => 'required|exists:cities,id',
            'pincode'   => 'required|digits_between:4,8',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'source'    => 'nullable|string|max:255',
            'customer_type' => 'nullable|string|max:50',
            'remarks'   => 'nullable|string',
        ], [
            // removed mobile_no.unique custom message
        ]);

        // --- Duplicate Check: same name (case-insensitive) + mobile_no ---
        $exists = Customer::whereRaw('LOWER(name) = ?', [strtolower($request->name)])
                          ->where('mobile_no', $request->mobile_no)
                          ->exists();
        if ($exists) {
            $msg = 'A customer with this name and mobile number already exists.';
            if ($request->ajax()) {
                return response()->json(['message' => $msg], 409);
            }
            return redirect()->back()->withInput()->with('create_customer_error', $msg);
        }

        $id = Auth::user()->id;
        $prefix = ($request->customer_type === 'Dealer') ? 'DLR-' : 'CUST-';

        try {
            DB::transaction(function () use ($request, $id, $prefix, &$customer) {
                $customer               = new Customer();
                $customer->name         = $request->name;
                $customer->email        = $request->email;
                $customer->mobile_no    = $request->mobile_no;
                $customer->alternative_no = $request->alternative_no;
                $customer->address      = $request->address;
                $customer->state_id     = $request->state;
                $customer->city_id      = $request->city;
                $customer->pincode      = $request->pincode;
                $customer->warehouse_id = $request->input('warehouse_id') ?? Auth::user()->warehouse_id ?? null;
                $customer->user_id      = $id;
                $customer->gst_no       = $request->gst_no;
                $customer->source       = $request->source;
                $customer->customer_type = $request->customer_type;
                $customer->remarks      = $request->remarks;
                $customer->save();

                // Use withTrashed() so soft-deleted customer IDs are counted,
                // preventing duplicate formatted_id after a customer is deleted.
                $maxSeq = Customer::withTrashed()
                                  ->where('customer_type', $request->customer_type)
                                  ->lockForUpdate()
                                  ->max('type_sequence') ?? 0;
                $nextSeq = $maxSeq + 1;

                $customer->type_sequence = $nextSeq;
                $customer->formatted_id  = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
                $customer->save();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Create Customer Error: ' . $e->getMessage());
            $msg = 'Failed to save customer: ' . (str_contains($e->getMessage(), 'Duplicate entry') ? 'A customer with similar details already exists.' : 'Database Error');
            if ($request->ajax()) {
                return response()->json(['message' => $msg, 'error' => $e->getMessage()], 409);
            }
            return redirect()->back()->withInput()->with('create_customer_error', $msg);
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Customer created successfully'], 200);
        }
        Session::flash('create_customer', 'Customer created successfully');
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
        $customer   =   Customer::find($id);
        $state      =   State::where('country_id','101')->pluck('name','id');
        if($customer->state_id) {
            $city = City::where('state_id', $customer->state_id)->pluck('name', 'id');
        } else {
            $city = [];
        }
        $sources    =   \App\Models\Source::where('status', 1)->orderBy('name')->get();
        $heading    =   "Edit Customer";
        return view('backend.modules.customer.edit',compact('customer','heading','state','city','sources'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    $this->validate($request, [
        'name'      => 'required',            
        'email'     => 'nullable|email',
        'mobile_no' => 'required|digits:10',
        'alternative_no' => 'nullable|digits:10',
            'address'   => 'nullable|string|max:255',
            'state'     => 'required|exists:states,id',
            'city'      => 'required|exists:cities,id',
            'pincode'   => 'required|digits_between:4,8',
            'source'    => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'customer_type' => 'nullable|string|max:50',
            'remarks'   => 'nullable|string',
        ], [
            // removed mobile_no.unique custom message
        ]);
    
        $customer = Customer::find($id);

        // Duplicate check: same name + mobile, excluding current customer
        $duplicate = Customer::whereRaw('LOWER(name) = ?', [strtolower($request->name)])
                             ->where('mobile_no', $request->mobile_no)
                             ->where('id', '!=', $id)
                             ->exists();
        if ($duplicate) {
            $msg = 'Another customer with this name and mobile number already exists.';
            if ($request->ajax()) {
                return response()->json(['message' => $msg], 409);
            }
            return redirect()->back()->withInput()->with('update_customer_error', $msg);
        }

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->mobile_no = $request->mobile_no;
        $customer->alternative_no = $request->alternative_no;
        $customer->address = $request->address;
        $customer->state_id = $request->state;
        $customer->city_id = $request->city;
        $customer->pincode = $request->pincode;
        $customer->warehouse_id = $request->input('warehouse_id') ?? Auth::user()->warehouse_id ?? $customer->warehouse_id;
        $customer->gst_no = $request->gst_no;
        $customer->source = $request->source;
        $customer->customer_type = $request->customer_type;
        $customer->remarks = $request->remarks;
        // Keep formatted_id unchanged — do NOT recalculate to avoid conflicts

        try {
            $customer->save();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Update Customer Error: ' . $e->getMessage());
            $msg = 'Failed to update customer: ' . (str_contains($e->getMessage(), 'Duplicate entry') ? 'A customer with similar details already exists.' : 'Database Error');
            if ($request->ajax()) {
                return response()->json(['message' => $msg, 'error' => $e->getMessage()], 409);
            }
            return redirect()->back()->withInput()->with('update_customer_error', $msg);
        } catch (\Exception $e) {
            \Log::error('Update Customer Exception: ' . $e->getMessage());
            $msg = 'Error: ' . $e->getMessage();
            if ($request->ajax()) {
                return response()->json(['message' => $msg], 409);
            }
            return redirect()->back()->withInput()->with('update_customer_error', $msg);
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Customer edited successfully'], 200);
        }
        Session::flash('edit_customer','Customer edited successfully');
        return redirect('customer');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $customer = \App\Models\Customer::findOrFail($id);
        // Prevent delete if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->back()->with('delete_customer', 'Cannot delete: Customer has invoices.');
        }
        $customer->delete();
        return redirect()->back()->with('delete_customer', 'Customer deleted successfully.');
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
            ->get();

        return response()->json($customers);
    }
    
}
