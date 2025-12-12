<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vendor::insert([
            [
                'name' =>'Tokopedia',
                'email' => 'tokopedia@gmail.com',
                'phone' =>'021-123123123',
                'contact_person'=>'toko', 
                'npwp' =>'1231313909313', 
                'address'=>'jakarta barat',
                'notes' => 'online',
                'is_active'=> 1,
            ],
            [
                'name' =>'Shopee',
                'email' => 'shopee@gmail.com',
                'phone' =>'021-321321321',
                'contact_person'=>'toko', 
                'npwp' =>'32132132131', 
                'address'=>'jakarta timur',
                'notes' => 'online',
                'is_active'=> 1,
            ],
            
            [
                'name' =>'Bukalpak',
                'email' => 'bukalpak@gmail.com',
                'phone' =>'021-9879879879',
                'contact_person'=>'toko', 
                'npwp' =>'9879879879', 
                'address'=>'jakarta pusat',
                'notes' => 'online',
                'is_active'=> 1,
            ],
            
        ]);
    }
}
