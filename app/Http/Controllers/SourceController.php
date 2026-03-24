<?php
namespace App\Http\Controllers;
use App\Models\Source;
use Illuminate\Http\Request;
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
            'name' => 'required',
            'status' => 'required|boolean',
        ]);
        $id = auth()->user()->id;
        $source = new Source();
        $source->name = $request->name;
        $source->status = $request->status;
        $source->user_id = $id;
        $source->save();
        \Session::flash('create_source', 'Source created successfully');
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
            'name' => 'required',
            'status' => 'required|boolean',
        ]);
        $source->name = $request->name;
        $source->status = $request->status;
        $source->user_id = auth()->user()->id;
        $source->save();
        \Session::flash('edit_source', 'Source edited successfully');
        return redirect('source');
    }

    public function destroy(Source $source)
    {
        $source->delete();
        \Session::flash('delete_source', 'Source deleted successfully');
        return redirect('source');
    }
    
}
