<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        $this->seedDefaultRoles();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['is_active', 'last_login_at']);
        });

        Schema::dropIfExists('roles');
    }

    /**
     * Create the built-in roles and give existing users administrator access.
     */
    private function seedDefaultRoles(): void
    {
        $now = now();

        /** @var array<int, array{name: string, slug: string, description: string, permissions: array<int, string>, is_system: bool}> $roles */
        $roles = config('roles.defaults', []);

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'permissions' => json_encode($role['permissions']),
                'is_system' => $role['is_system'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');

        if ($adminId !== null) {
            DB::table('users')->whereNull('role_id')->update(['role_id' => $adminId]);
        }
    }
};
