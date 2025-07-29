<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'CUSTOMER', 'description' => 'Customer'],
            ['name' => 'BEAUTICIAN', 'description' => 'Beautician'],
            ['name' => 'MANAGER', 'description' => 'Manager'],
            ['name' => 'ADMIN', 'description' => 'Admin'],
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
