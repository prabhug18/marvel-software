<?php

namespace App\Exports\Sheets;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Category::select('name')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Valid Category Names'];
    }

    public function title(): string
    {
        return 'Valid Categories';
    }
}
