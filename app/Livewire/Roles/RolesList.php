<?php

namespace App\Livewire\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rôles & permissions')]
class RolesList extends Component
{
    public string $search = '';

    public ?int $editingRoleId = null;

    public string $role_name = '';

    public string $role_description = '';

    /** @var array<string, bool> */
    public array $permissions = [];

    public ?int $deletingRoleId = null;

    public function mount(): void
    {
        $this->authorizeAction();
        $this->resetPermissions();
    }

    public function createRole(): void
    {
        $this->authorizeAction();

        $this->resetForm();
        $this->dispatch('modal-show', name: 'role-modal');
    }

    public function editRole(int $roleId): void
    {
        $this->authorizeAction();

        $role = Role::query()->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->role_name = $role->name;
        $this->role_description = $role->description ?? '';
        $this->resetPermissions();

        if ($role->is_system) {
            foreach (array_keys($this->permissions) as $permission) {
                $this->permissions[$permission] = true;
            }
        } else {
            foreach ($role->permissions ?? [] as $permission) {
                if (array_key_exists($permission, $this->permissions)) {
                    $this->permissions[$permission] = true;
                }
            }
        }

        $this->resetErrorBag();
        $this->dispatch('modal-show', name: 'role-modal');
    }

    public function saveRole(): void
    {
        $this->authorizeAction();

        $validated = $this->validate([
            'role_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($this->editingRoleId),
            ],
            'role_description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['boolean'],
        ]);

        $selected = array_keys(array_filter(
            $this->permissions,
            fn (bool $enabled): bool => $enabled,
        ));

        if ($this->editingRoleId !== null) {
            $role = Role::query()->findOrFail($this->editingRoleId);

            $role->update([
                'name' => $validated['role_name'],
                'description' => $validated['role_description'],
                'permissions' => $role->is_system
                    ? ['*']
                    : array_values(array_intersect($selected, Role::permissionKeys())),
            ]);

            session()->flash('status', 'Le rôle « '.$role->name.' » a été mis à jour.');
        } else {
            $role = Role::query()->create([
                'name' => $validated['role_name'],
                'slug' => $this->uniqueSlug($validated['role_name']),
                'description' => $validated['role_description'],
                'permissions' => array_values(array_intersect($selected, Role::permissionKeys())),
                'is_system' => false,
            ]);

            session()->flash('status', 'Le rôle « '.$role->name.' » a été créé.');
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'role-modal');
    }

    public function duplicateRole(int $roleId): void
    {
        $this->authorizeAction();

        $role = Role::query()->findOrFail($roleId);
        $name = $role->name.' (copie)';

        $copy = Role::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'description' => $role->description,
            'permissions' => $role->is_system
                ? Role::permissionKeys()
                : ($role->permissions ?? []),
            'is_system' => false,
        ]);

        session()->flash(
            'status',
            'Le rôle « '.$copy->name.' » a été créé à partir de « '.$role->name.' ».',
        );
    }

    public function confirmDelete(int $roleId): void
    {
        $this->authorizeAction();

        $role = Role::query()->findOrFail($roleId);

        if ($role->is_system) {
            session()->flash('error', 'Le rôle système ne peut pas être supprimé.');

            return;
        }

        $this->deletingRoleId = $role->id;
        $this->resetErrorBag('delete');
        $this->dispatch('modal-show', name: 'delete-role-modal');
    }

    public function deleteRole(): void
    {
        $this->authorizeAction();

        if ($this->deletingRoleId === null) {
            return;
        }

        $role = Role::query()->withCount('users')->findOrFail($this->deletingRoleId);

        if ($role->is_system) {
            $this->addError('delete', 'Le rôle système ne peut pas être supprimé.');

            return;
        }

        if ($role->users_count > 0) {
            $this->addError(
                'delete',
                'Ce rôle est attribué à '.$role->users_count.' utilisateur(s). Réattribuez-les avant de supprimer ce rôle.',
            );

            return;
        }

        $role->delete();
        $this->deletingRoleId = null;

        session()->flash('status', 'Le rôle a été supprimé.');
        $this->dispatch('modal-close', name: 'delete-role-modal');
    }

    public function selectAllPermissions(string $group): void
    {
        $this->toggleGroupPermissions($group, true);
    }

    public function clearAllPermissions(string $group): void
    {
        $this->toggleGroupPermissions($group, false);
    }

    public function render(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        return view('livewire.roles.list', [
            'roles' => $roles,
            'permissionGroups' => Role::permissionGroups(),
        ]);
    }

    private function toggleGroupPermissions(string $group, bool $enabled): void
    {
        $this->authorizeAction();

        $groups = Role::permissionGroups();

        if (! isset($groups[$group])) {
            return;
        }

        foreach (array_keys($groups[$group]['permissions']) as $permission) {
            $this->permissions[$permission] = $enabled;
        }
    }

    private function resetForm(): void
    {
        $this->reset('editingRoleId', 'role_name', 'role_description', 'deletingRoleId');
        $this->resetPermissions();
        $this->resetErrorBag();
    }

    private function resetPermissions(): void
    {
        $this->permissions = [];

        foreach (Role::permissionKeys() as $permission) {
            $this->permissions[$permission] = false;
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $suffix = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function authorizeAction(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission('roles_manage'), 403);
    }
}
