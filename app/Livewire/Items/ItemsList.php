<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\User;
use App\Support\Tva;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Catalogue des articles')]
class ItemsList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingItemId = null;

    public ?int $deletingItemId = null;

    public string $name = '';

    public string $description = '';

    public string $unit = '';

    public string $unit_price = '0';

    public string $tva_rate = '20';

    public function mount(): void
    {
        $this->authorizeAction('items_view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeAction('items_create');

        $this->resetForm();
        $this->dispatch('modal-show', name: 'item-modal');
    }

    public function openEditModal(int $itemId): void
    {
        $this->authorizeAction('items_edit');

        $item = Item::query()->findOrFail($itemId);

        $this->editingItemId = $item->id;
        $this->name = $item->name;
        $this->description = $item->description ?? '';
        $this->unit = $item->unit ?? '';
        $this->unit_price = number_format((float) $item->unit_price, 2, '.', '');
        $this->tva_rate = (string) (int) $item->tva_rate;

        $this->resetErrorBag();
        $this->dispatch('modal-show', name: 'item-modal');
    }

    public function saveItem(): void
    {
        $editing = $this->editingItemId !== null;

        $this->authorizeAction($editing ? 'items_edit' : 'items_create');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'tva_rate' => ['required', 'numeric', Rule::in(Tva::RATES)],
        ]);

        if ($editing) {
            $item = Item::query()->findOrFail($this->editingItemId);
            $item->update($validated);

            session()->flash('status', 'L’article '.$item->name.' a été mis à jour.');
        } else {
            $item = Item::query()->create($validated);

            session()->flash('status', 'L’article '.$item->name.' a été créé.');
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'item-modal');
    }

    public function confirmDelete(int $itemId): void
    {
        $this->authorizeAction('items_delete');

        $this->deletingItemId = $itemId;
        $this->resetErrorBag('delete');
        $this->dispatch('modal-show', name: 'delete-item-modal');
    }

    public function deleteItem(): void
    {
        $this->authorizeAction('items_delete');

        if ($this->deletingItemId === null) {
            return;
        }

        $item = Item::query()->withCount('invoiceItems')->findOrFail($this->deletingItemId);

        if ($item->invoice_items_count > 0) {
            $this->addError(
                'delete',
                'Cet article est utilisé dans '.$item->invoice_items_count.' ligne(s) de facture. Supprimez ou détachez ces lignes avant de continuer.',
            );

            return;
        }

        $item->delete();
        $this->deletingItemId = null;

        session()->flash('status', 'L’article a été supprimé.');
        $this->dispatch('modal-close', name: 'delete-item-modal');
    }

    public function render(): View
    {
        $items = Item::query()
            ->withCount('invoiceItems')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('unit', 'like', $search);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $deletingItem = $this->deletingItemId !== null
            ? Item::query()->find($this->deletingItemId)
            : null;

        return view('livewire.items.list', [
            'items' => $items,
            'deletingItem' => $deletingItem,
            'tvaRates' => Tva::options(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(
            'editingItemId',
            'name',
            'description',
            'unit',
            'unit_price',
            'tva_rate',
        );

        $this->unit_price = '0';
        $this->tva_rate = '20';
        $this->resetErrorBag();
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }
}
