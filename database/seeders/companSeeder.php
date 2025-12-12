<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class companSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::insert([
            [
                'code'=>'ABC',
                'name'=> 'PT. ABC INDONESIA',
                'email' => 'abc@gmail.com',
                'phone'=>'021-',
                'pic' =>'budi',
                'address' =>'Jakarta',
            ],
            [
                'code'=>'XYZ',
                'name'=> 'PT. XYZ INDONESIA',
                'email' => 'xyz@gmail.com',
                'phone'=>'021-',
                'pic' =>'jono',
                'address' =>'Jakarta',
            ],
            [
                'code'=>'PQR',
                'name'=> 'PT. PQR INDONESIA',
                'email' => 'pqr@gmail.com',
                'phone'=>'021-',
                'pic' =>'johan',
                'address' =>'Jakarta',
            ],
            [
                'code'=>'DIY',
                'name'=> 'PT. DIY INDONESIA',
                'email' => 'diy@gmail.com',
                'phone'=>'021-',
                'pic' =>'maman',
                'address' =>'Jakarta',
            ],
            [
                'code'=>'TUV',
                'name'=> 'PT. TUV INDONESIA',
                'email' => 'tuv@gmail.com',
                'phone'=>'021-',
                'pic' =>'maman',
                'address' =>'Jakarta',
            ],
            
            
        ]);
    }
}
