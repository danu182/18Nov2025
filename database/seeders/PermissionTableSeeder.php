<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'user-create',
            'user-edit',
            'user-list',
            'user-delete',
            'permissions-list',
            'permissions-delete',
            'permissions-edit',
            'permissions-create',
            'home-create',
            'home-edit',
            'home-list',
            'home-delete',
            'uom-list',
            'uom-create',
            'uom-edit',
            'uom-delete',
            'company-list',
            'company-edit',
            'company-delete',
            'company-create',
            'department-list',
            'department-edit',
            'department-create',
            'Approve-Level-1',
            'Approve-Level-2',

        ];

        foreach ($permissions as $permission) {
             Permission::create(['name' => $permission]);
        }
    }
}
