<?php

namespace App\Exports\Sheets;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BrandsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Brand::select('name')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Valid Brand Names'];
    }

    public function title(): string
    {
        return 'Valid Brands';
    }
}
