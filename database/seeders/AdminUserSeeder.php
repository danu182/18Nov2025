<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- DEFINISIKAN PERMISSIONS ---
        // Definisikan semua permission yang akan digunakan di aplikasi Anda
        $arrayOfPermissions = [
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'home-list',
            'home-create',
            'home-edit',
            'home-delete',
        ];
        
        // Buat permissions jika belum ada
        foreach ($arrayOfPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- 1. SETUP ROLE & USER ADMIN ---
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        
        // Berikan SEMUA permissions yang ada ke role Admin
        $adminRole->syncPermissions(Permission::all());

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
            'name' => 'Admin', 
            'password' => Hash::make('123') // Ganti dengan password yang lebih aman
            ]
        );

        // Assign role Admin ke user Admin
        $adminUser->assignRole($adminRole);

        // =======================================================
        // --- 2. SETUP ROLE DEFAULT (Customer) ---
        // Ini adalah role yang akan diberikan otomatis saat user daftar
        // =======================================================
        $customerRole = Role::firstOrCreate(['name' => 'Customer']);
        
        // Definisikan permission spesifik untuk role Customer
        $customerPermissions = [
            'product-list',
            'home-list',
        ];
        $customerRole->syncPermissions($customerPermissions);

        // --- 3. BUAT CONTOH USER DEFAULT ---
        $customerUser = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
            'name' => 'Customer User', 
            'password' => Hash::make('123'), // Ganti dengan password yang lebih aman
            'company_id'=> 1,
            'department_id'=>1,
            ]
        );
        $customerUser->assignRole($customerRole);


        // untuk approval-level-1
        $customerRole1 = Role::firstOrCreate(['name' => 'approve-pr-level-1']);
        
        // Definisikan permission spesifik untuk role Customer
        $customerPermissions1 = [
            'Approve-Level-1',
        ];
        $customerRole1->syncPermissions($customerPermissions1);

        // --- 3. BUAT CONTOH USER DEFAULT ---
        $customerUser1 = User::firstOrCreate(
            ['email' => 'budi@gmail.com'],
            [
            'name' => 'approve-pr-level-1', 
            'password' => Hash::make('123'), // Ganti dengan password yang lebih aman
            'company_id'=> 1,
            'department_id'=>1,
            ]
        );
        $customerUser1->assignRole($customerRole1);


        // untuk approval-level-2
        $customerRole2 = Role::firstOrCreate(['name' => 'approve-pr-level-2']);
        
        // Definisikan permission spesifik untuk role Customer
        $customerPermissions2 = [
            'Approve-Level-2',
        ];
        $customerRole2->syncPermissions($customerPermissions2);

        // --- 3. BUAT CONTOH USER DEFAULT ---
        $customerUser2 = User::firstOrCreate(
            ['email' => 'wati@gmail.com'],
            [
            'name' => 'approve-pr-level-2', 
            'password' => Hash::make('123'), // Ganti dengan password yang lebih aman
            'company_id'=> 1,
            'department_id'=>1,
            ]
        );
        $customerUser2->assignRole($customerRole2);


    }
}
