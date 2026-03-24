<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Term;
use Illuminate\Support\Facades\Auth;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::orderBy('created_at', 'desc')->paginate(20);
        return view('backend.admin.terms.index', compact('terms'));
    }

    public function create()
    {
        return view('backend.admin.terms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'active' => 'sometimes|boolean',
        ]);
        $data['created_by'] = Auth::id();
        $data['active'] = $request->boolean('active');
        

        Term::create($data);
        return redirect()->route('terms.index')->with('success', 'Term created.');
    }

    public function edit($id)
    {
        $term = Term::findOrFail($id);
        return view('backend.admin.terms.edit', compact('term'));
    }

    public function update(Request $request, $id)
    {
        $term = Term::findOrFail($id);
        $data = $request->validate([
            'content' => 'required|string',
            'active' => 'sometimes|boolean',
        ]);
        $data['active'] = $request->boolean('active');

        $term->update($data);
        return redirect()->route('terms.index')->with('success', 'Term updated.');
    }

    public function destroy($id)
    {
        $term = Term::findOrFail($id);
        $term->delete();
        return redirect()->route('terms.index')->with('success', 'Term deleted.');
    }
}
