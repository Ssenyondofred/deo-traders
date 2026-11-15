<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::create([
            'slug' => 'inventory.adjust',
            'name' => 'Penyesuaian Stok',
            'module' => 'inventory',
            'description' => 'Saldo awal dan koreksi stok (Admin)',
        ]);

        $admin = Role::where('slug', 'admin')->first();
        $admin?->permissions()->attach($permission->id);
    }

    public function down(): void
    {
        $permission = Permission::where('slug', 'inventory.adjust')->first();
        if ($permission) {
            Role::where('slug', 'admin')->first()?->permissions()->detach($permission->id);
            $permission->delete();
        }
    }
};
