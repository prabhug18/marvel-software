<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
       
        $status = [
            'Active',
            'Disabled',
            'User Logged In',
            'User Logged Out',
            'User Created',
            'User Updated',
            'User Deleted', 
            'Customer Created',
            'Customer Updated',
            'Customer Deleted'                       
         ];

        foreach ($status as $statusVal) {
              Status::createQuietly(['name' => $statusVal]);
         }
    }
}
