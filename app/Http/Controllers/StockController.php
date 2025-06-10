<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StockImport;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //        
        $heading    =   "Stock View";
        $stock = Stock::select('model', 'category_id', 'brand_id')
                    ->selectRaw('SUM(qty) as total_qty')
                    ->groupBy('model', 'category_id', 'brand_id')
                    ->orderBy('updated_at', 'desc')
                    ->get();
        return view('backend.modules.stocks.index', compact('heading','stock'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $heading        =   "Stock Creation";
        return view('backend.modules.stocks.create', compact('heading'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
         $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|array',
            'warehouse_id.*' => 'required|exists:warehouses,id',
            'stock' => 'required|array',
            'stock.*' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $product = \App\Models\Product::with(['category', 'brand'])->findOrFail($request->product_id);

        // Custom duplicate check
        $duplicateErrors = [];
        foreach ($request->warehouse_id as $index => $warehouseId) {
            $exists = \App\Models\Stock::where('warehouse_id', $warehouseId)
                ->where('category_id', $product->category_id)
                ->where('brand_id', $product->brand_id)
                ->where('model', $product->model)
                ->exists();

            if ($exists) {
                $duplicateErrors["warehouse_id.$index"] = ["Already this stock details exists in warehouse "];
            }
        }

        if (!empty($duplicateErrors)) {
            return response()->json([
                'errors' => $duplicateErrors
            ], 422);
        }

        foreach ($request->warehouse_id as $index => $warehouseId) {
            Stock::create([
                'warehouse_id' => $warehouseId,
                'category_id'  => $product->category_id,
                'brand_id'     => $product->brand_id,
                'model'        => $product->model,
                'qty'          => $request->stock[$index],
                'user_id'      => Auth::id(),
            ]);
        }

        return response()->json(['message' => 'Product created successfully!']);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $heading        =   "Stock Edit";
        return view('backend.modules.stocks.edit', compact('heading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function exportPage()
    {
        $heading    =   "Stock Excel Export";        
        return view('backend.modules.stocks.bulkExport', compact('heading'));
    }

    public function export()
    {
        return Excel::download(new StockExport, 'stocks.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $result = Excel::import(new StockImport, $request->file('file'));
            // If StockImport returns an array, it's a partial success
            if (is_array($result) && isset($result['partial_success'])) {
                return response()->json(['errors' => [
                    'import' => $result['import'],
                    'success_count' => $result['success_count']
                ]], 200);
            }
            return response()->json(['message' => 'Bulk stock uploaded successfully!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ValidationException: all rows failed
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Stock Import Error: ' . $e->getMessage());
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX endpoint to check available stock for a product model in a warehouse
     */
    public function checkStock(Request $request)
    {
        $model = $request->input('model');
        $warehouseId = $request->input('warehouse_id');
        $stock = \App\Models\Stock::where('model', $model)
            ->where('warehouse_id', $warehouseId)
            ->sum('qty');
        return response()->json(['available_stock' => $stock]);
    }

    public function __construct()
    {
        $this->middleware('permission:stock-list|stock-create|stock-edit|stock-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:stock-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:stock-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:stock-delete', ['only' => ['destroy']]);
    }
}

