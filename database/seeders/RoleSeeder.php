<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var array<int, array{name: string, slug: string, description: string, permissions: array<int, string>, is_system: bool}> $roles */
        $roles = config('roles.defaults', []);

        foreach ($roles as $defaultRole) {
            Role::query()->updateOrCreate(
                ['slug' => $defaultRole['slug']],
                [
                    'name' => $defaultRole['name'],
                    'description' => $defaultRole['description'],
                    'permissions' => $defaultRole['permissions'],
                    'is_system' => $defaultRole['is_system'],
                ],
            );
        }

        $adminRole = Role::query()->where('slug', 'admin')->first();

        if ($adminRole !== null) {
            User::query()->whereNull('role_id')->update(['role_id' => $adminRole->id]);
        }
    }
}
