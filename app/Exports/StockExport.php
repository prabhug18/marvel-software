<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\StockMatrixSheet;
use App\Exports\Sheets\CategoriesSheet;
use App\Exports\Sheets\BrandsSheet;

class StockExport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new StockMatrixSheet();
        $sheets[] = new CategoriesSheet();
        $sheets[] = new BrandsSheet();

        return $sheets;
    }
}