<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\State;
use App\Models\City;
use App\Models\Source;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EnquiryController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:enquiry-list|enquiry-create|enquiry-edit|enquiry-delete', ['only' => ['index','store']]);
         $this->middleware('permission:enquiry-create', ['only' => ['create','store']]);
         $this->middleware('permission:enquiry-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:enquiry-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Enquiry::with(['state', 'city', 'warehouse', 'assignee']);

        if (!Auth::user()->hasRole('Admin')) {
            $query->where('warehouse_id', Auth::user()->warehouse_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->orderBy('updated_at', 'desc')->get();
        $heading = "Enquiry Management";
        
        return view('backend.modules.enquiry.index', compact('heading', 'enquiries'));
    }

    public function create()
    {
        $states = State::where('country_id', '101')->get();
        $sources = Source::where('status', 1)->orderBy('name')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        $heading = "Add New Enquiry";
        
        return view('backend.modules.enquiry.create', compact('heading', 'states', 'sources', 'warehouses', 'users'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|digits:10',
            'email' => 'nullable|email',
            'state' => 'required|exists:states,id',
            'city' => 'required|exists:cities,id',
            'source' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $enquiry = new Enquiry();
        $enquiry->name = $request->name;
        $enquiry->mobile_no = $request->mobile_no;
        $enquiry->email = $request->email;
        $enquiry->address = $request->address;
        $enquiry->state_id = $request->state;
        $enquiry->city_id = $request->city;
        $enquiry->source = $request->source;
        $enquiry->product_interest = $request->product_interest;
        $enquiry->brand_interest = $request->brand_interest;
        $enquiry->remarks = $request->remarks;
        $enquiry->assigned_to = $request->assigned_to;
        $enquiry->warehouse_id = $request->warehouse_id ?? Auth::user()->warehouse_id;
        $enquiry->user_id = Auth::id();
        $enquiry->status = 'new';
        $enquiry->save();

        Session::flash('create_enquiry', 'Enquiry created successfully');
        return redirect()->route('enquiries.index');
    }

    public function show($id)
    {
        $enquiry = Enquiry::with(['state', 'city', 'warehouse', 'assignee', 'user'])->findOrFail($id);
        $heading = "Enquiry Details: " . $enquiry->enquiry_number;
        return view('backend.modules.enquiry.show', compact('heading', 'enquiry'));
    }

    public function edit($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $states = State::where('country_id', '101')->get();
        $cities = City::where('state_id', $enquiry->state_id)->get();
        $sources = Source::where('status', 1)->orderBy('name')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        $heading = "Edit Enquiry";
        
        return view('backend.modules.enquiry.edit', compact('heading', 'enquiry', 'states', 'cities', 'sources', 'warehouses', 'users'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|digits:10',
            'email' => 'nullable|email',
            'state' => 'required|exists:states,id',
            'city' => 'required|exists:cities,id',
            'source' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'required|in:new,contacted,converted,closed',
        ]);

        $enquiry = Enquiry::findOrFail($id);
        $enquiry->name = $request->name;
        $enquiry->mobile_no = $request->mobile_no;
        $enquiry->email = $request->email;
        $enquiry->address = $request->address;
        $enquiry->state_id = $request->state;
        $enquiry->city_id = $request->city;
        $enquiry->source = $request->source;
        $enquiry->product_interest = $request->product_interest;
        $enquiry->brand_interest = $request->brand_interest;
        $enquiry->remarks = $request->remarks;
        $enquiry->assigned_to = $request->assigned_to;
        $enquiry->warehouse_id = $request->warehouse_id ?? Auth::user()->warehouse_id;
        $enquiry->status = $request->status;
        $enquiry->save();

        Session::flash('edit_enquiry', 'Enquiry updated successfully');
        return redirect()->route('enquiries.index');
    }

    public function destroy($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->delete();
        Session::flash('delete_enquiry', 'Enquiry deleted successfully');
        return redirect()->route('enquiries.index');
    }

    public function convertToLead($id)
    {
        $enquiry = Enquiry::findOrFail($id);

        if ($enquiry->status === 'converted') {
            return redirect()->back()->with('error', 'This enquiry has already been converted to a lead.');
        }

        return DB::transaction(function () use ($enquiry) {
            $lead = new Lead();
            $lead->enquiry_id = $enquiry->id;
            $lead->name = $enquiry->name;
            $lead->mobile_no = $enquiry->mobile_no;
            $lead->email = $enquiry->email;
            $lead->address = $enquiry->address;
            $lead->state_id = $enquiry->state_id;
            $lead->city_id = $enquiry->city_id;
            $lead->source = $enquiry->source;
            $lead->product_interest = $enquiry->product_interest;
            $lead->brand_interest = $enquiry->brand_interest;
            $lead->assigned_to = $enquiry->assigned_to;
            $lead->warehouse_id = $enquiry->warehouse_id;
            $lead->user_id = Auth::id();
            $lead->status = 'new';
            $lead->priority = 'medium';
            $lead->save();

            $enquiry->status = 'converted';
            $enquiry->save();

            Session::flash('success', 'Enquiry converted to Lead successfully');
            return redirect()->route('leads.show', $lead->id);
        });
    }
}
