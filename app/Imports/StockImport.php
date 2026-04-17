<?php
namespace App\Imports;

use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Auth;

class StockImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Stock Matrix' => new class implements ToCollection {
                public function collection(Collection $rows)
                {
                    $header = $rows->first();
                    // Assuming first 6 columns are: Category, Brand, Model, Model No, Invoice No, Purchase Rate
                    $warehouseNames = $header->slice(6)->values()->map(function($val) {
                        return trim((string)$val);
                    })->toArray(); 

                    $hasError = false;
                    $errorMessages = [];
                    foreach ($rows->skip(1) as $rowIndex => $row) {
                        // Function to clean and trim values
                        $clean = function($val) {
                            if ($val === null) return null;
                            $v = trim((string)$val);
                            return preg_replace('/[\xF0-\xF7]...|[\xE0-\xEF]..|[\xC2-\xDF].|[\x00-\x1F\x7F]/', '', str_replace("\xc2\xa0", ' ', $v));
                        };

                        $categoryName = $clean($row->get(0));
                        $brandName = $clean($row->get(1));
                        $modelName = $clean($row->get(2));
                        $modelNo = ($row->get(3) !== null && $clean($row->get(3)) !== '') ? $clean($row->get(3)) : null;

                        // Skip completely empty rows
                        if (empty($categoryName) && empty($brandName) && empty($modelName)) {
                            continue;
                        }

                        // New Fields
                        $invoiceNo = $clean($row->get(4));
                        $purchaseRate = is_numeric($row->get(5)) ? $row->get(5) : null;

                        // Use case-insensitive and trimmed search for Category and Brand
                        $category = $categoryName ? Category::where('name', 'like', $categoryName)->first() : null;
                        $brand = $brandName ? Brand::where('name', 'like', $brandName)->first() : null;
                        
                        $catId = $category ? $category->id : null;
                        $brandId = $brand ? $brand->id : null;

                        // Check if product exists for this category, brand, model, and model_no
                        $productExists = \App\Models\Product::withTrashed()->where([
                            'category_id' => $catId,
                            'brand_id' => $brandId,
                            'model' => $modelName,
                            'model_no' => $modelNo,
                        ])->exists();

                        // Validation for missing category/brand/model or product
                        $missing = [];
                        if (!$category) $missing[] = "Category '$categoryName' Not Found";
                        if (!$brand) $missing[] = "Brand '$brandName' Not Found";
                        if (empty($modelName)) $missing[] = 'Model Name: Empty';
                        if (!$productExists) {
                            $missing[] = "Product Not Found (Search: CatID=" . ($catId ?? 'NULL') . ", BrandID=" . ($brandId ?? 'NULL') . ", Model='$modelName', ModelNo=" . ($modelNo ?? 'NULL') . ")";
                        }
                        
                        // Fix for actual Excel row number (skip(1) leaves indices as 1, 2, 3...)
                        $excelRowNumber = $rowIndex + 1; 
                        
                        if (!empty($missing)) {
                            $msg = 'StockImport Row '.$excelRowNumber.': '. implode(', ', $missing);
                            $hasError = true;
                            $errorMessages[] = $msg;
                            continue;
                        }

                        foreach ($warehouseNames as $i => $warehouseName) {
                            $warehouse = Warehouse::where('name', 'like', $warehouseName)->first();
                            $qtyIdx = $i + 6; // Correct index after values() 
                            $qtyToAdd = (int) $row->get($qtyIdx);

                            if ($warehouse && $category && $brand) {
                                $stock = Stock::where([
                                    'warehouse_id' => $warehouse->id,
                                    'category_id'  => $category->id,
                                    'brand_id'     => $brand->id,
                                    'model'        => $modelName,
                                    'model_no'     => $modelNo,
                                ])->first();

                                $data = [
                                    'qty'          => $qtyToAdd,
                                    'invoice_no'   => $invoiceNo,
                                    'purchase_rate' => $purchaseRate,
                                    'user_id'      => Auth::id() ?? 1,
                                ];

                                if ($stock) {
                                    $stock->update($data);
                                } else {
                                    Stock::create(array_merge($data, [
                                        'warehouse_id' => $warehouse->id,
                                        'category_id'  => $category->id,
                                        'brand_id'     => $brand->id,
                                        'model'        => $modelName,
                                        'model_no'     => $modelNo,
                                    ]));
                                }
                            }
                        }
                    }
                    
                    if ($hasError && count($errorMessages) > 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'import' => $errorMessages
                        ]);
                    }
                }
            }
        ];
    }
}