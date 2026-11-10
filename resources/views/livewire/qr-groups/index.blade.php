<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">QR Codes</flux:heading>
            <flux:subheading class="mt-1">Sélectionnez des clients et des documents, regroupez-les puis imprimez tous leurs QR codes.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="newGroup">
            Nouveau groupe
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="flex gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                <flux:button variant="{{ $tab === 'documents' ? 'primary' : 'ghost' }}" size="sm" wire:click="setTab('documents')">Documents</flux:button>
                <flux:button variant="{{ $tab === 'clients' ? 'primary' : 'ghost' }}" size="sm" wire:click="setTab('clients')">Clients</flux:button>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher..." clearable class="flex-1" />
                @if ($tab === 'documents')
                    <flux:select wire:model.live="typeFilter" placeholder="Tous les types">
                        <flux:select.option value="">Tous les types</flux:select.option>
                        @foreach ($documentTypes as $type)
                            <flux:select.option :value="$type->value">{{ $type->title() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="sm" variant="subtle" icon="check" wire:click="selectCurrentPage">Tout sélectionner sur la page</flux:button>
                <flux:button size="sm" variant="subtle" icon="check-circle" wire:click="selectAllResults">Tout sélectionner (résultats)</flux:button>
                <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="clearSelection">Tout désélectionner</flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                            <th class="py-2 pe-3 w-10"></th>
                            <th class="py-2 pe-3">Élément</th>
                            <th class="py-2 pe-3">Détails</th>
                            <th class="py-2 pe-3">QR</th>
                            <th class="py-2 text-end">Archive</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($tab === 'documents')
                            @forelse ($documents as $history)
                                <tr wire:key="qr-document-{{ $history->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                                    <td class="py-2 pe-3">
                                        <input
                                            type="checkbox"
                                            wire:model.live="selected"
                                            value="document:{{ $history->id }}"
                                            wire:key="qr-document-check-{{ $history->id }}"
                                            class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                                        >
                                    </td>
                                    <td class="py-2 pe-3">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $history->title }}</div>
                                        <div class="mt-0.5 flex items-center gap-2">
                                            <flux:badge color="zinc" size="sm">{{ \App\Livewire\Documents\DocumentType::tryFrom($history->document_type)?->title() ?? $history->document_type }}</flux:badge>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $history->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">
                                        <div>{{ $history->client?->displayName() ?? data_get($history->data, 'client.name') ?? '—' }}</div>
                                        @php
                                            $number = $history->invoice?->number ?? data_get($history->data, 'invoice_number');
                                            $total = data_get($history->data, 'total');
                                        @endphp
                                        @if ($number || $total)
                                            <div class="text-xs">
                                                @if ($number) N° {{ $number }} @endif
                                                @if ($number && $total) · @endif
                                                @if ($total) {{ number_format((float) $total, 2, ',', ' ') }} DH @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-2 pe-3">
                                        {!! \App\Support\QrCode::svg(route('archive.documents.show', $history->public_token), 40) !!}
                                    </td>
                                    <td class="py-2 text-end">
                                        <flux:button variant="ghost" size="sm" icon="eye" :href="route('archive.documents.show', $history->public_token)" target="_blank">Voir</flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-zinc-500">Aucun document trouvé.</td>
                                </tr>
                            @endforelse
                        @else
                            @forelse ($clients as $client)
                                <tr wire:key="qr-client-{{ $client->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                                    <td class="py-2 pe-3">
                                        <input
                                            type="checkbox"
                                            wire:model.live="selected"
                                            value="client:{{ $client->id }}"
                                            wire:key="qr-client-check-{{ $client->id }}"
                                            class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                                        >
                                    </td>
                                    <td class="py-2 pe-3">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $client->displayName() }}</div>
                                        <div class="mt-0.5">
                                            <flux:badge color="zinc" size="sm">{{ $client->isCompany() ? 'Entreprise' : 'Particulier' }}</flux:badge>
                                        </div>
                                    </td>
                                    <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">
                                        <div>{{ $client->email ?: ($client->phone ?: '—') }}</div>
                                        <div class="text-xs">{{ $client->document_histories_count }} document(s)</div>
                                    </td>
                                    <td class="py-2 pe-3">
                                        {!! \App\Support\QrCode::svg(route('archive.clients.show', $client->public_token), 40) !!}
                                    </td>
                                    <td class="py-2 text-end">
                                        <flux:button variant="ghost" size="sm" icon="eye" :href="route('archive.clients.show', $client->public_token)" target="_blank">Voir</flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-zinc-500">Aucun client trouvé.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @php($paginator = $tab === 'documents' ? $documents : $clients)
            @if ($paginator && $paginator->hasPages())
                <div class="flex justify-end gap-2 pt-2">
                    <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$paginator->onFirstPage()">Précédent</flux:button>
                    <flux:text size="sm" class="self-center">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</flux:text>
                    <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $paginator->hasMorePages()">Suivant</flux:button>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <flux:card class="space-y-4 lg:sticky lg:top-6">
                <div>
                    <flux:heading size="lg">Sélection</flux:heading>
                    <flux:text size="sm" class="mt-1">{{ $selectedDocumentCount }} document(s) · {{ $selectedClientCount }} client(s)</flux:text>
                </div>

                @if ($selectedItems->isEmpty())
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Cochez des documents ou des clients pour créer un groupe QR.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($selectedItems->take(8) as $item)
                            <li wire:key="selected-{{ $item['key'] }}" class="flex items-start justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['label'] }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['subtitle'] }}</div>
                                </div>
                                <button type="button" wire:click="removeSelection('{{ $item['key'] }}')" class="text-zinc-400 hover:text-red-500" title="Retirer">
                                    <flux:icon.x-mark class="size-4" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    @if ($selectedItems->count() > 8)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">+ {{ $selectedItems->count() - 8 }} autre(s) élément(s)</p>
                    @endif
                @endif

                <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:input wire:model="name" label="Nom du groupe" placeholder="Ex. Lots clients janvier" />
                    @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <flux:textarea wire:model="description" label="Description" rows="2" placeholder="Optionnel" />
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    @error('selected') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <flux:button variant="primary" icon="qr-code" class="w-full" wire:click="createGroup">
                        Créer le groupe QR
                    </flux:button>
                </div>
            </flux:card>
        </div>
    </div>

    <div class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-800">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Groupes enregistrés</flux:heading>
                <flux:subheading>Chaque groupe conserve les QR codes de ses éléments.</flux:subheading>
            </div>
        </div>

        @if ($groups->isEmpty())
            <p class="rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700">
                Aucun groupe QR pour le moment. Sélectionnez des éléments ci-dessus pour commencer.
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($groups as $group)
                    <div wire:key="qr-group-{{ $group->id }}" class="flex flex-col gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $group->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $group->items_count }} élément(s)
                                    · {{ $group->document_items_count }} doc.
                                    · {{ $group->client_items_count }} client(s)
                                </div>
                            </div>
                            {!! \App\Support\QrCode::svg(route('qr-groups.show', $group->public_token), 56) !!}
                        </div>

                        @if ($group->description)
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $group->description }}</p>
                        @endif

                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $group->user?->name ?? 'Utilisateur supprimé' }} · {{ $group->created_at?->format('d/m/Y H:i') }}
                        </div>

                        <div class="mt-auto flex flex-wrap gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" :href="route('qr-groups.show', $group->public_token)" wire:navigate>Voir</flux:button>
                            <flux:button size="sm" variant="ghost" icon="printer" :href="route('qr-groups.print', $group->public_token)" target="_blank">Imprimer</flux:button>
                            @if ($group->user_id === auth()->id() || auth()->user()?->role?->is_system)
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Supprimer ce groupe QR ?">Supprimer</flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
