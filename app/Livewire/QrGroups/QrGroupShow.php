<?php

namespace App\Livewire\QrGroups;

use App\Models\QrGroup;
use App\Models\QrGroupItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Groupe QR')]
class QrGroupShow extends Component
{
    public QrGroup $group;

    public function mount(string $token): void
    {
        $this->authorizeAccess();

        $this->group = QrGroup::query()
            ->where('public_token', $token)
            ->with(['items', 'user'])
            ->firstOrFail();
    }

    public function deleteGroup(): void
    {
        $this->authorizeAccess();

        $user = auth()->user();

        abort_unless(
            $this->group->user_id === $user?->id || (bool) $user?->role?->is_system,
            403,
        );

        $name = $this->group->name;
        $this->group->delete();

        session()->flash('status', 'Le groupe QR « '.$name.' » a été supprimé.');

        $this->redirectRoute('qr-groups.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.qr-groups.show', [
            'documents' => $this->group->items
                ->where('item_type', QrGroupItem::TYPE_DOCUMENT)
                ->values(),
            'clients' => $this->group->items
                ->where('item_type', QrGroupItem::TYPE_CLIENT)
                ->values(),
            'groupUrl' => route('qr-groups.show', $this->group->public_token),
        ]);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view')
            || $user?->hasPermission('clients_view')
            || $user?->hasPermission('invoices_view'),
            403,
        );
    }
}
