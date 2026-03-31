<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->get();
        $heading = "Vendor List";
        return view('backend.modules.vendors.index', compact('vendors', 'heading'));
    }

    public function create()
    {
        $heading = "Add Vendor";
        return view('backend.modules.vendors.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gst_no' => 'nullable|string|max:50',
        ]);

        Vendor::create($request->all());

        return response()->json(['message' => 'Vendor created successfully!']);
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $heading = "Edit Vendor";
        return view('backend.modules.vendors.edit', compact('vendor', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gst_no' => 'nullable|string|max:50',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update($request->all());

        return response()->json(['message' => 'Vendor updated successfully!']);
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return redirect()->back()->with('success', 'Vendor deleted successfully.');
    }

    /**
     * AJAX: Search vendors for auto-suggestion
     */
    public function search(Request $request)
    {
        $q = $request->input('q');
        $vendors = Vendor::where('name', 'like', "%$q%")
            ->orWhere('mobile', 'like', "%$q%")
            ->limit(10)
            ->get(['id', 'name', 'mobile', 'gst_no']);

        return response()->json($vendors);
    }
}
