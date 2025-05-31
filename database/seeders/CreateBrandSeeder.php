<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $brand = Brand::createQuietly([
            'name' => 'HP',
            'user_id' => '1'            
        ]);

        $brand = Brand::createQuietly([
            'name' => 'DELL',
            'user_id' => '1'            
        ]);
    }
}
