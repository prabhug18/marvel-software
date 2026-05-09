<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Enquiry;
use App\Models\Customer;
use App\Models\State;
use App\Models\City;
use App\Models\Source;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LeadController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete', ['only' => ['index','store']]);
         $this->middleware('permission:lead-create', ['only' => ['create','store']]);
         $this->middleware('permission:lead-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:lead-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Lead::with(['state', 'city', 'warehouse', 'assignee']);

        if (!Auth::user()->hasRole('Admin')) {
            $query->where('warehouse_id', Auth::user()->warehouse_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        $leads = $query->orderBy('updated_at', 'desc')->get();
        $heading = "Lead Management";
        
        return view('backend.modules.lead.index', compact('heading', 'leads'));
    }

    public function create(Request $request)
    {
        $enquiry = null;
        if ($request->enquiry_id) {
            $enquiry = Enquiry::find($request->enquiry_id);
        }

        $states = State::where('country_id', '101')->get();
        $sources = Source::where('status', 1)->orderBy('name')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        $heading = "Add New Lead";
        
        return view('backend.modules.lead.create', compact('heading', 'states', 'sources', 'warehouses', 'users', 'enquiry'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|digits:10',
            'email' => 'nullable|email',
            'state' => 'required|exists:states,id',
            'city' => 'required|exists:cities,id',
            'expected_value' => 'nullable|numeric',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:new,follow_up,negotiation,converted,lost',
            'next_follow_up' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = new Lead();
        $lead->enquiry_id = $request->enquiry_id;
        $lead->name = $request->name;
        $lead->mobile_no = $request->mobile_no;
        $lead->email = $request->email;
        $lead->address = $request->address;
        $lead->state_id = $request->state;
        $lead->city_id = $request->city;
        $lead->source = $request->source;
        $lead->product_interest = $request->product_interest;
        $lead->brand_interest = $request->brand_interest;
        $lead->expected_value = $request->expected_value;
        $lead->priority = $request->priority;
        $lead->status = $request->status;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->assigned_to = $request->assigned_to;
        $lead->warehouse_id = $request->warehouse_id ?? Auth::user()->warehouse_id;
        $lead->user_id = Auth::id();
        $lead->save();

        if ($request->enquiry_id) {
            Enquiry::where('id', $request->enquiry_id)->update(['status' => 'converted']);
        }

        Session::flash('create_lead', 'Lead created successfully');
        return redirect()->route('leads.index');
    }

    public function show($id)
    {
        $lead = Lead::with(['state', 'city', 'warehouse', 'assignee', 'user', 'followUps.user', 'enquiry', 'customer'])->findOrFail($id);
        $heading = "Lead Details: " . $lead->lead_number;
        return view('backend.modules.lead.show', compact('heading', 'lead'));
    }

    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $states = State::where('country_id', '101')->get();
        $cities = City::where('state_id', $lead->state_id)->get();
        $sources = Source::where('status', 1)->orderBy('name')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        $heading = "Edit Lead";
        
        return view('backend.modules.lead.edit', compact('heading', 'lead', 'states', 'cities', 'sources', 'warehouses', 'users'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|digits:10',
            'email' => 'nullable|email',
            'state' => 'required|exists:states,id',
            'city' => 'required|exists:cities,id',
            'expected_value' => 'nullable|numeric',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:new,follow_up,negotiation,converted,lost',
            'next_follow_up' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->name = $request->name;
        $lead->mobile_no = $request->mobile_no;
        $lead->email = $request->email;
        $lead->address = $request->address;
        $lead->state_id = $request->state;
        $lead->city_id = $request->city;
        $lead->source = $request->source;
        $lead->product_interest = $request->product_interest;
        $lead->brand_interest = $request->brand_interest;
        $lead->expected_value = $request->expected_value;
        $lead->priority = $request->priority;
        $lead->status = $request->status;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->assigned_to = $request->assigned_to;
        $lead->warehouse_id = $request->warehouse_id ?? Auth::user()->warehouse_id;
        $lead->save();

        Session::flash('edit_lead', 'Lead updated successfully');
        return redirect()->route('leads.index');
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        Session::flash('delete_lead', 'Lead deleted successfully');
        return redirect()->route('leads.index');
    }

    public function addFollowUp(Request $request, $id)
    {
        $this->validate($request, [
            'follow_up_date' => 'required|date',
            'notes' => 'required|string',
            'outcome' => 'required|in:interested,not_interested,no_response,callback',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead = Lead::findOrFail($id);

        $followUp = new LeadFollowUp();
        $followUp->lead_id = $lead->id;
        $followUp->follow_up_date = $request->follow_up_date;
        $followUp->notes = $request->notes;
        $followUp->outcome = $request->outcome;
        $followUp->next_follow_up = $request->next_follow_up;
        $followUp->user_id = Auth::id();
        $followUp->save();

        // Update lead status and next follow up date
        $lead->next_follow_up = $request->next_follow_up;
        if ($lead->status === 'new') {
            $lead->status = 'follow_up';
        }
        $lead->save();

        Session::flash('success', 'Follow-up added successfully');
        return redirect()->route('leads.show', $lead->id);
    }

    public function convertToCustomer(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        if ($lead->customer_id) {
            return redirect()->back()->with('error', 'This lead is already associated with a customer.');
        }

        // Check if customer already exists with this mobile
        $customer = Customer::where('mobile_no', $lead->mobile_no)->first();

        return DB::transaction(function () use ($lead, $customer, $request) {
            if (!$customer) {
                $customer = new Customer();
                $customer->name = $lead->name;
                $customer->email = $lead->email;
                $customer->mobile_no = $lead->mobile_no;
                $customer->address = $lead->address;
                $customer->state_id = $lead->state_id;
                $customer->city_id = $lead->city_id;
                $customer->warehouse_id = $lead->warehouse_id;
                $customer->user_id = Auth::id();
                $customer->source = $lead->source;
                $customer->customer_type = 'Customer';
                $customer->save();

                // Format ID logic (simplified)
                $prefix = 'CUST-';
                $nextSeq = Customer::withTrashed()->where('customer_type', 'Customer')->max('type_sequence') + 1;
                $customer->type_sequence = $nextSeq;
                $customer->formatted_id = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
                $customer->save();
            }

            $lead->customer_id = $customer->id;
            $lead->status = 'converted';
            $lead->save();

            Session::flash('success', 'Lead converted to Customer successfully');
            return redirect()->route('customer.index'); // Go to customer list
        });
    }
}
