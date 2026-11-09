<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gestion des clients')]
class ClientsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public ?int $editingClientId = null;

    public ?int $deletingClientId = null;

    public string $type = Client::TYPE_COMPANY;

    public string $company_name = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $ice = '';

    public string $if_number = '';

    public string $rc = '';

    public string $cnss = '';

    public string $cin = '';

    public string $address = '';

    public string $city = '';

    public string $postal_code = '';

    public string $phone = '';

    public string $email = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->authorizeAction('clients_view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeAction('clients_create');

        $this->resetForm();
        $this->dispatch('modal-show', name: 'client-modal');
    }

    public function openEditModal(int $clientId): void
    {
        $this->authorizeAction('clients_edit');

        $client = Client::query()->findOrFail($clientId);

        $this->editingClientId = $client->id;
        $this->type = $client->type;
        $this->company_name = $client->company_name ?? '';
        $this->first_name = $client->first_name ?? '';
        $this->last_name = $client->last_name ?? '';
        $this->ice = $client->ice ?? '';
        $this->if_number = $client->if_number ?? '';
        $this->rc = $client->rc ?? '';
        $this->cnss = $client->cnss ?? '';
        $this->cin = $client->cin ?? '';
        $this->address = $client->address ?? '';
        $this->city = $client->city ?? '';
        $this->postal_code = $client->postal_code ?? '';
        $this->phone = $client->phone ?? '';
        $this->email = $client->email ?? '';
        $this->notes = $client->notes ?? '';

        $this->resetErrorBag();
        $this->dispatch('modal-show', name: 'client-modal');
    }

    public function saveClient(): void
    {
        $editing = $this->editingClientId !== null;

        $this->authorizeAction($editing ? 'clients_edit' : 'clients_create');

        $validated = $this->validate($this->rules());

        if ($this->type === Client::TYPE_COMPANY) {
            $validated['first_name'] = null;
            $validated['last_name'] = null;
            $validated['cin'] = null;
        } else {
            $validated['company_name'] = null;
            $validated['ice'] = null;
            $validated['if_number'] = null;
            $validated['rc'] = null;
            $validated['cnss'] = null;
        }

        if ($editing) {
            $client = Client::query()->findOrFail($this->editingClientId);
            $client->update($validated);

            session()->flash('status', 'La fiche client de '.$client->displayName().' a été mise à jour.');
        } else {
            $client = Client::query()->create($validated);

            session()->flash('status', 'Le client '.$client->displayName().' a été créé.');
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'client-modal');
    }

    public function confirmDelete(int $clientId): void
    {
        $this->authorizeAction('clients_delete');

        $this->deletingClientId = $clientId;
        $this->resetErrorBag('delete');
        $this->dispatch('modal-show', name: 'delete-client-modal');
    }

    public function deleteClient(): void
    {
        $this->authorizeAction('clients_delete');

        if ($this->deletingClientId === null) {
            return;
        }

        $client = Client::query()
            ->withCount(['invoices', 'documentHistories'])
            ->findOrFail($this->deletingClientId);

        if ($client->invoices_count > 0) {
            $this->addError(
                'delete',
                'Ce client est lié à '.$client->invoices_count.' facture(s). Supprimez ou réattribuez ces factures avant de continuer.',
            );

            return;
        }

        if ($client->document_histories_count > 0) {
            $this->addError(
                'delete',
                'Ce client est lié à '.$client->document_histories_count.' document(s). Supprimez ces documents avant de continuer.',
            );

            return;
        }

        $client->delete();
        $this->deletingClientId = null;

        session()->flash('status', 'Le client a été supprimé.');
        $this->dispatch('modal-close', name: 'delete-client-modal');
    }

    public function render(): View
    {
        $clients = Client::query()
            ->withCount('invoices')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('company_name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('ice', 'like', $search)
                        ->orWhere('if_number', 'like', $search)
                        ->orWhere('cin', 'like', $search);
                });
            })
            ->when($this->typeFilter !== '', fn ($query) => $query->where('type', $this->typeFilter))
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10);

        $deletingClient = $this->deletingClientId !== null
            ? Client::query()->find($this->deletingClientId)
            : null;

        return view('livewire.clients.list', [
            'clients' => $clients,
            'deletingClient' => $deletingClient,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        $rules = [
            'type' => ['required', Rule::in(Client::TYPES)],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->type === Client::TYPE_COMPANY) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['ice'] = ['nullable', 'string', 'max:15'];
            $rules['if_number'] = ['nullable', 'string', 'max:50'];
            $rules['rc'] = ['nullable', 'string', 'max:50'];
            $rules['cnss'] = ['nullable', 'string', 'max:50'];
        } else {
            $rules['first_name'] = ['required', 'string', 'max:100'];
            $rules['last_name'] = ['required', 'string', 'max:100'];
            $rules['cin'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }

    private function resetForm(): void
    {
        $this->reset(
            'editingClientId',
            'type',
            'company_name',
            'first_name',
            'last_name',
            'ice',
            'if_number',
            'rc',
            'cnss',
            'cin',
            'address',
            'city',
            'postal_code',
            'phone',
            'email',
            'notes',
        );

        $this->type = Client::TYPE_COMPANY;
        $this->resetErrorBag();
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }
}
