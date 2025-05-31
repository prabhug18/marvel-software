<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $category = Category::createQuietly([
            'name' => 'Laptop',
            'user_id' => '1'            
        ]);

        $category = Category::createQuietly([
            'name' => 'System',
            'user_id' => '1'            
        ]);
    }
}
