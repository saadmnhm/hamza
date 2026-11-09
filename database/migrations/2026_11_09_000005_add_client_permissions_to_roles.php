<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The client permissions introduced with the client management module.
     *
     * @var array<int, string>
     */
    private array $newPermissions = [
        'clients_view',
        'clients_create',
        'clients_edit',
        'clients_delete',
    ];

    public function up(): void
    {
        foreach (config('roles.defaults', []) as $defaultRole) {
            $role = DB::table('roles')->where('slug', $defaultRole['slug'])->first();

            if ($role === null || $role->is_system) {
                continue;
            }

            $existing = json_decode((string) $role->permissions, true) ?: [];
            $granted = array_values(array_intersect($defaultRole['permissions'], $this->newPermissions));

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique(array_merge($existing, $granted)))),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('roles')->get() as $role) {
            $existing = json_decode((string) $role->permissions, true) ?: [];
            $remaining = array_values(array_diff($existing, $this->newPermissions));

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($remaining),
            ]);
        }
    }
};
