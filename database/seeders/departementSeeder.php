<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class departementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::insert([
            [
                'name'=>'FINANCE',
                'code'=>'FA',
            ],
            [
                'name'=>'HUMAN RESOURCE',
                'code'=>'HR',
            ],
            [
                'name'=>'IT DEPARTEMAN',
                'code'=>'IT',
            ],
            [
                'name'=>'MARKETING',
                'code'=>'MKT',
            ],
            [
                'name'=>'SALES',
                'code'=>'SLS',
            ],
            [
                'name'=>'GENERAL AFFAIR',
                'code'=>'GA',
            ],
            
        ]);
    }
}
