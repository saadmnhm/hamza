<?php

namespace App\Livewire\Utilisateurs;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gestion des utilisateurs')]
class Utilisateurs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public ?int $editingUserId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $role_id = '';

    public function mount(): void
    {
        $this->authorizeAction('users_view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeAction('users_invite');

        $this->resetForm();
        $this->dispatch('modal-show', name: 'user-modal');
    }

    public function openEditModal(int $userId): void
    {
        $this->authorizeAction('users_edit');

        $user = User::query()->findOrFail($userId);

        $this->authorizeTarget($user);

        $this->editingUserId = $user->id;
        [$this->first_name, $this->last_name] = $this->splitName($user->name);
        $this->email = $user->email;
        $this->role_id = $user->role_id !== null ? (string) $user->role_id : '';
        $this->resetErrorBag();
        $this->dispatch('modal-show', name: 'user-modal');
    }

    public function saveUser(): void
    {
        $editing = $this->editingUserId !== null;

        $this->authorizeAction($editing ? 'users_edit' : 'users_invite');

        $canAssignRole = auth()->user()?->hasPermission('roles_manage') ?? false;
        $user = null;

        if ($editing) {
            $user = User::query()->findOrFail($this->editingUserId);
            $this->authorizeTarget($user);
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
        ];

        if ($canAssignRole) {
            $rules['role_id'] = ['required', 'integer', Rule::exists('roles', 'id')];
        }

        $validated = $this->validate($rules);

        $name = trim($validated['first_name'].' '.$validated['last_name']);

        if ($editing && $user !== null) {
            $attributes = [
                'name' => $name,
                'email' => $validated['email'],
            ];

            if ($canAssignRole) {
                $attributes['role_id'] = (int) $validated['role_id'];
            }

            $user->update($attributes);

            session()->flash('status', 'Le profil de '.$user->name.' a été mis à jour.');
        } else {
            $roleId = $canAssignRole
                ? (int) $validated['role_id']
                : Role::query()->where('slug', 'employe')->value('id');

            $user = User::query()->create([
                'name' => $name,
                'email' => $validated['email'],
                'password' => Str::password(32),
                'role_id' => $roleId,
                'is_active' => true,
            ]);

            $user->sendEmailVerificationNotification();

            session()->flash('status', 'Une invitation a été envoyée à '.$user->email.'.');
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'user-modal');
    }

    public function toggleStatus(int $userId): void
    {
        $this->authorizeAction('users_edit');

        if ($userId === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');

            return;
        }

        $user = User::query()->findOrFail($userId);
        $this->authorizeTarget($user);

        $user->update(['is_active' => ! $user->is_active]);

        session()->flash(
            'status',
            $user->is_active
                ? 'Le compte de '.$user->name.' a été activé.'
                : 'Le compte de '.$user->name.' a été désactivé.',
        );
    }

    public function resendInvitation(int $userId): void
    {
        $this->authorizeAction('users_invite');

        $user = User::query()->findOrFail($userId);
        $this->authorizeTarget($user);

        if ($user->email_verified_at !== null) {
            session()->flash('error', 'Ce compte est déjà vérifié.');

            return;
        }

        $user->sendEmailVerificationNotification();

        session()->flash('status', "L'invitation a été renvoyée à ".$user->email.'.');
    }

    public function sendPasswordReset(int $userId): void
    {
        $this->authorizeAction('users_edit');

        $user = User::query()->findOrFail($userId);
        $this->authorizeTarget($user);

        $status = Password::sendResetLink(['email' => $user->email]);

        $sent = $status === Password::RESET_LINK_SENT;

        session()->flash(
            $sent ? 'status' : 'error',
            $sent
                ? 'Un lien de réinitialisation a été envoyé à '.$user->email.'.'
                : "Impossible d'envoyer le lien de réinitialisation.",
        );
    }

    public function cancelInvitation(int $userId): void
    {
        $this->authorizeAction('users_invite');

        $user = User::query()->findOrFail($userId);
        $this->authorizeTarget($user);

        if ($user->email_verified_at !== null) {
            session()->flash('error', 'Seules les invitations en attente peuvent être annulées.');

            return;
        }

        $email = $user->email;
        $user->delete();

        session()->flash('status', "L'invitation de ".$email.' a été annulée.');
    }

    public function render(): View
    {
        $users = User::query()
            ->with('role')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->where('role_id', $this->roleFilter))
            ->when($this->statusFilter !== '', function ($query): void {
                match ($this->statusFilter) {
                    'active' => $query->whereNotNull('email_verified_at')->where('is_active', true),
                    'pending' => $query->whereNull('email_verified_at'),
                    'inactive' => $query->where('is_active', false),
                    default => null,
                };
            })
            ->latest()
            ->paginate(10);

        return view('livewire.utilisateurs.users', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset('editingUserId', 'first_name', 'last_name', 'email', 'role_id');
        $this->resetErrorBag();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }

    /**
     * Protect system-role accounts from being managed by regular users.
     */
    private function authorizeTarget(User $target): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($target->role?->is_system && ! $user?->hasPermission('roles_manage')) {
            abort(403);
        }
    }
}
