<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Exports\StockExport;
use App\Exports\DetailedStockExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StockImport;

class StockController extends Controller{


    // ...existing code...
    // ...existing code...

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //        
        $heading    =   "Stock View";
        $stock = Stock::select('model', 'model_no', 'category_id', 'brand_id')
                    ->selectRaw('SUM(qty) as total_qty')
                    ->groupBy('model', 'model_no', 'category_id', 'brand_id')
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
            'purchase_date' => 'nullable|date',
            'vendor_id' => 'nullable|exists:vendors,id',
            'purchased_from' => 'nullable|string',
            'purchase_rate' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'serial_no' => 'nullable|array',
            'serial_no.*' => 'nullable|string',
            'bulk_serials' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Process Serial Numbers (Individual + Bulk)
        $serialsArray = $request->serial_no ?? [];
        if ($request->filled('bulk_serials')) {
            // Split by newline, comma, or space
            $bulkSerials = preg_split('/[\n\r,]+/', $request->bulk_serials);
            foreach ($bulkSerials as $s) {
                $trimmed = trim($s);
                if (!empty($trimmed)) {
                    $serialsArray[] = $trimmed;
                }
            }
        }
        
        $serialsArray = array_unique($serialsArray);

        if (empty($serialsArray)) {
            return response()->json(['errors' => ['serial_no' => ['At least one serial number is required.']]], 422);
        }

        $product = \App\Models\Product::with(['category', 'brand'])->findOrFail($request->product_id);

        // Custom duplicate check - check if serial no already exists anywhere
        $duplicateErrors = [];
        foreach ($serialsArray as $index => $serialNo) {
            $exists = \App\Models\Stock::where('serial_no', $serialNo)->exists();

            if ($exists) {
                $duplicateErrors["serial_no"] = ["Serial Number '$serialNo' already exists in the system."];
                break; // Stop at first error for bulk
            }
        }

        if (!empty($duplicateErrors)) {
            return response()->json([
                'errors' => $duplicateErrors
            ], 422);
        }

        foreach ($serialsArray as $serialNo) {
            Stock::create([
                'warehouse_id'   => $request->warehouse_id,
                'category_id'    => $product->category_id,
                'brand_id'       => $product->brand_id,
                'model'          => $product->model,
                'model_no'       => $product->model_no,
                'qty'            => 1, // Fixed to 1 for serial tracked items
                'serial_no'      => $serialNo,
                'vendor_id'      => $request->vendor_id,
                'purchase_date'  => $request->purchase_date,
                'purchased_from' => $request->purchased_from,
                'purchase_rate'  => $request->purchase_rate,
                'remarks'        => $request->remarks,
                'user_id'        => Auth::id(),
            ]);
        }

        return response()->json(['message' => 'Stock created successfully!']);
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
        return Excel::download(new StockExport, 'stocks_matrix.xlsx');
    }

    public function exportDetailed()
    {
        return Excel::download(new DetailedStockExport, 'stocks_detailed_' . date('Y-m-d') . '.xlsx');
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
        $serialNoRaw = $request->input('serial_no');

        $query = \App\Models\Stock::where('model', $model)
            ->where('warehouse_id', $warehouseId);

        $totalStock = (clone $query)->sum('qty');

        $unavailableSerials = [];
        if ($serialNoRaw) {
            // Support both comma and ampersand as separators for validation
            $serialsToCheck = preg_split('/[,&]+/', $serialNoRaw, -1, PREG_SPLIT_NO_EMPTY);
            $serialsToCheck = array_map('trim', $serialsToCheck);
            
            foreach ($serialsToCheck as $sn) {
                if ($sn === '') continue;
                $exists = (clone $query)->where('serial_no', $sn)->where('qty', '>', 0)->exists();
                if (!$exists) {
                    $unavailableSerials[] = $sn;
                }
            }
        }

        return response()->json([
            'available_stock' => $totalStock,
            'unavailable_serials' => $unavailableSerials,
            'success' => empty($unavailableSerials) && ($totalStock > 0)
        ]);
    }

    public function __construct()
    {
        $this->middleware('permission:stock-list|stock-create|stock-edit|stock-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:stock-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:stock-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:stock-delete', ['only' => ['destroy']]);
    }

    /**
     * AJAX: Get warehouse-wise stock for a product
     */
    public function warehouseStock(Request $request)
    {
        $stocks = Stock::where('category_id', $request->category_id)
            ->where('brand_id', $request->brand_id)
            ->where('model', $request->model)
            ->where('model_no', $request->model_no)
            ->where('qty', '>', 0) // Only active stock
            ->select('warehouse_id', \DB::raw('SUM(qty) as total_qty'), \DB::raw('GROUP_CONCAT(serial_no SEPARATOR ", ") as serials'))
            ->groupBy('warehouse_id')
            ->with('warehouse')
            ->get();

        return response()->json($stocks->map(function($stock) {
            return [
                'warehouse' => $stock->warehouse ? $stock->warehouse->name : 'N/A',
                'warehouse_id' => $stock->warehouse_id,
                'qty' => $stock->total_qty,
                'serials' => $stock->serials,
            ];
        }));
    }

    /**
     * AJAX: Update warehouse stock quantity
     */
    public function updateWarehouseStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string',
            'model_no' => 'nullable|string',
            'qty' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = trim($request->model);
        // Update all matching stock rows for this warehouse/category/brand/model
        $updated = Stock::where('warehouse_id', $request->warehouse_id)
            ->where('category_id', $request->category_id)
            ->where('brand_id', $request->brand_id)
            ->whereRaw('LOWER(model) = ?', [strtolower($model)])
            ->where('model_no', $request->model_no)
            ->update(['qty' => $request->qty]);

        // If no row was updated, create a new one
        if ($updated === 0) {
            Stock::create([
                'warehouse_id' => $request->warehouse_id,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'model' => $model,
                'model_no' => $request->model_no,
                'qty' => $request->qty,
                'user_id' => Auth::id(),
            ]);
        }
        return response()->json(['message' => 'Stock updated successfully!']);
    }
}

