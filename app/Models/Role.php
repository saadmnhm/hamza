<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<int, string>|null $permissions
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_system',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The permission catalogue grouped by functional area.
     *
     * @return array<string, array{label: string, icon: string, permissions: array<string, string>}>
     */
    public static function permissionGroups(): array
    {
        /** @var array<string, array{label: string, icon: string, permissions: array<string, string>}> $groups */
        $groups = config('roles.groups', []);

        return $groups;
    }

    /**
     * Every permission key defined in the catalogue.
     *
     * @return array<int, string>
     */
    public static function permissionKeys(): array
    {
        return collect(static::permissionGroups())
            ->flatMap(fn (array $group): array => array_keys($group['permissions']))
            ->values()
            ->all();
    }

    /**
     * Determine whether the role grants the given permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->is_system || in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Number of catalogue permissions granted to a non-system role.
     */
    public function permissionCount(): int
    {
        return count(array_intersect($this->permissions ?? [], static::permissionKeys()));
    }

    /**
     * Flux badge colour used to represent the role.
     */
    public function color(): string
    {
        /** @var array<string, string> $colors */
        $colors = config('roles.colors', []);

        return $colors[$this->slug] ?? $colors['default'] ?? 'zinc';
    }

    /**
     * Heroicon name used to represent the role.
     */
    public function icon(): string
    {
        /** @var array<string, string> $icons */
        $icons = config('roles.icons', []);

        return $icons[$this->slug] ?? $icons['default'] ?? 'users';
    }
}
