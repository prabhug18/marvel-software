<?php
namespace App\Http\Controllers;
use App\Models\Source;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
class SourceController extends Controller
{    
    public function index()
    {
        $sources = Source::all();
        $heading = 'Source List';
        return view('backend.modules.source.index', compact('sources', 'heading'));
    }
    
    public function create()
    {
        $heading = 'Add New Source';
        return view('backend.modules.source.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:sources,name',
            'status' => 'required|boolean',
        ]);
        $id = auth()->user()->id;
        $source = new Source();
        $source->name = $request->name;
        $source->status = $request->status;
        $source->user_id = $id;
        $source->save();
        \Session::flash('success', 'Source created successfully');
        return redirect('source');
    }

    public function show(Source $source)
    {
        $heading = 'Source Details';
        return view('backend.modules.source.show', compact('source', 'heading'));
    }
    
    public function edit(Source $source)
    {
        $heading = 'Edit Source';
        return view('backend.modules.source.edit', compact('source', 'heading'));
    }

    public function update(Request $request, Source $source)
    {
        $this->validate($request, [
            'name' => 'required|unique:sources,name,' . $source->id,
            'status' => 'required|boolean',
        ]);
        $source->name = $request->name;
        $source->status = $request->status;
        $source->user_id = auth()->user()->id;
        $source->save();
        \Session::flash('success', 'Source edited successfully');
        return redirect('source');
    }

    public function destroy(Source $source)
    {
        // Check if source name is used in customers
        $customerCount = Customer::where('source', $source->name)->count();

        if ($customerCount > 0) {
            Session::flash('error', 'Cannot delete Source: It is currently assigned to ' . $customerCount . ' Customers.');
            return redirect('source');
        }

        $source->delete();
        Session::flash('success', 'Source deleted successfully');
        return redirect('source');
    }
    
}
