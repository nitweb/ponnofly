<?php

namespace Database\Seeders\Admin;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = new Admin();
        $admin->name = 'Super Admin';
        $admin->email = 'admin@admin.com';
        $admin->job_title = 'Super Admin';
        $admin->role_id = Role::where('slug', Role::SUPER_ADMIN_SLUG)->value('id');
        $admin->password = bcrypt('admin@123##');
        $admin->save();
    }
}
