<?php

namespace App\Livewire\QrGroups;

use App\Livewire\Documents\DocumentType;
use App\Models\Client;
use App\Models\DocumentHistory;
use App\Models\QrGroup;
use App\Models\QrGroupItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Groupes QR')]
class QrGroups extends Component
{
    use WithPagination;

    public string $tab = 'documents';

    public string $search = '';

    public string $typeFilter = '';

    /** @var list<string> */
    public array $selected = [];

    public string $name = '';

    public string $description = '';

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['documents', 'clients'], true) ? $tab : 'documents';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function newGroup(): void
    {
        $this->reset(['selected', 'name', 'description']);
        $this->resetValidation();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function removeSelection(string $key): void
    {
        $this->selected = array_values(array_filter(
            $this->selected,
            fn (mixed $value): bool => $value !== $key,
        ));
    }

    public function selectCurrentPage(): void
    {
        $keys = $this->tab === 'clients'
            ? $this->clientsQuery()->forPage($this->getPage(), 10)->pluck('id')->map(fn (mixed $id): string => 'client:'.(int) $id)->all()
            : $this->documentsQuery()->forPage($this->getPage(), 10)->pluck('id')->map(fn (mixed $id): string => 'document:'.(int) $id)->all();

        /** @var list<string> $keys */
        $this->selected = array_values(array_unique(array_merge($this->selected, $keys)));
    }

    public function selectAllResults(): void
    {
        $keys = $this->tab === 'clients'
            ? $this->clientsQuery()->pluck('id')->map(fn (mixed $id): string => 'client:'.(int) $id)->all()
            : $this->documentsQuery()->pluck('id')->map(fn (mixed $id): string => 'document:'.(int) $id)->all();

        /** @var list<string> $keys */
        $this->selected = array_values(array_unique(array_merge($this->selected, $keys)));
    }

    public function createGroup(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['string'],
        ]);

        $selected = collect($this->selected)
            ->filter(fn (string $key): bool => Str::contains($key, ':'))
            ->unique()
            ->values();

        $documentIds = $this->idsFor($selected, 'document');
        $clientIds = $this->idsFor($selected, 'client');

        $documents = DocumentHistory::query()
            ->with(['client', 'invoice'])
            ->whereIn('id', $documentIds)
            ->get()
            ->keyBy('id');

        $clients = Client::query()
            ->whereIn('id', $clientIds)
            ->get()
            ->keyBy('id');

        if ($documents->isEmpty() && $clients->isEmpty()) {
            $this->addError('selected', 'Sélectionnez au moins un document ou un client existant.');

            return;
        }

        $group = DB::transaction(function () use ($validated, $documents, $clients): QrGroup {
            $group = QrGroup::query()->create([
                'user_id' => (int) auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($documents as $document) {
                $group->items()->create([
                    'item_type' => QrGroupItem::TYPE_DOCUMENT,
                    'item_id' => $document->id,
                    'public_token' => $document->public_token ?? (string) Str::uuid(),
                    'label' => $document->title,
                    'meta' => [
                        'document_type' => $document->document_type,
                        'type_label' => DocumentType::tryFrom($document->document_type)?->title(),
                        'number' => data_get($document, 'invoice.number') ?? data_get($document->data, 'invoice_number'),
                        'client' => $document->client?->displayName() ?? data_get($document->data, 'client.name'),
                        'total' => data_get($document->data, 'total'),
                        'date' => $document->created_at?->format('d/m/Y'),
                    ],
                ]);
            }

            foreach ($clients as $client) {
                $group->items()->create([
                    'item_type' => QrGroupItem::TYPE_CLIENT,
                    'item_id' => $client->id,
                    'public_token' => $client->public_token ?? (string) Str::uuid(),
                    'label' => $client->displayName(),
                    'meta' => [
                        'client_type' => $client->type,
                        'type_label' => $client->isCompany() ? 'Entreprise' : 'Particulier',
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'city' => $client->city,
                    ],
                ]);
            }

            return $group;
        });

        $this->reset(['selected', 'name', 'description']);

        session()->flash('status', 'Le groupe QR « '.$group->name.' » a été créé.');

        $this->redirectRoute('qr-groups.show', ['token' => $group->public_token], navigate: true);
    }

    public function deleteGroup(int $groupId): void
    {
        $this->authorizeAccess();

        $group = QrGroup::query()->findOrFail($groupId);
        $user = auth()->user();

        abort_unless(
            $group->user_id === $user?->id || (bool) $user?->role?->is_system,
            403,
        );

        $group->delete();

        session()->flash('status', 'Le groupe QR a été supprimé.');
    }

    public function render(): View
    {
        $selected = collect($this->selected)->unique()->values();

        $documentIds = $this->idsFor($selected, 'document');
        $clientIds = $this->idsFor($selected, 'client');

        $selectedDocuments = DocumentHistory::query()->whereIn('id', $documentIds)->get()->keyBy('id');
        $selectedClients = Client::query()->whereIn('id', $clientIds)->get()->keyBy('id');

        $selectedItems = $selected
            ->map(function (string $key) use ($selectedDocuments, $selectedClients): ?array {
                if (Str::startsWith($key, 'document:')) {
                    $document = $selectedDocuments->get((int) Str::after($key, 'document:'));

                    if (! $document instanceof DocumentHistory) {
                        return null;
                    }

                    return [
                        'key' => $key,
                        'type' => 'document',
                        'label' => $document->title,
                        'subtitle' => DocumentType::tryFrom($document->document_type)?->title() ?? 'Document',
                    ];
                }

                $client = $selectedClients->get((int) Str::after($key, 'client:'));

                if (! $client instanceof Client) {
                    return null;
                }

                return [
                    'key' => $key,
                    'type' => 'client',
                    'label' => $client->displayName(),
                    'subtitle' => $client->isCompany() ? 'Entreprise' : 'Particulier',
                ];
            })
            ->filter()
            ->values();

        return view('livewire.qr-groups.index', [
            'documents' => $this->tab === 'documents' ? $this->documentsQuery()->paginate(10) : null,
            'clients' => $this->tab === 'clients' ? $this->clientsQuery()->paginate(10) : null,
            'groups' => QrGroup::query()->with('user')->withCount(['items', 'documentItems', 'clientItems'])->latest()->take(12)->get(),
            'documentTypes' => DocumentType::cases(),
            'selectedItems' => $selectedItems,
            'selectedDocumentCount' => count($documentIds),
            'selectedClientCount' => count($clientIds),
        ]);
    }

    /**
     * @param  Collection<int, string>  $selected
     * @return list<int>
     */
    private function idsFor(Collection $selected, string $type): array
    {
        $ids = $selected
            ->filter(fn (string $key): bool => Str::startsWith($key, $type.':'))
            ->map(fn (string $key): int => (int) Str::after($key, $type.':'))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        return array_values($ids);
    }

    /**
     * @return Builder<DocumentHistory>
     */
    private function documentsQuery(): Builder
    {
        return DocumentHistory::query()
            ->with(['client', 'invoice'])
            ->when($this->typeFilter !== '', fn (Builder $query): Builder => $query->where('document_type', $this->typeFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('document_type', 'like', $search)
                        ->orWhereHas('client', function (Builder $query) use ($search): void {
                            $query->where('company_name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search);
                        })
                        ->orWhereHas('invoice', fn (Builder $query): Builder => $query->where('number', 'like', $search));
                });
            })
            ->latest();
    }

    /**
     * @return Builder<Client>
     */
    private function clientsQuery(): Builder
    {
        return Client::query()
            ->withCount('documentHistories')
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('company_name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('ice', 'like', $search)
                        ->orWhere('cin', 'like', $search);
                });
            })
            ->latest();
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view') || $user?->hasPermission('clients_view'),
            403,
        );
    }
}
