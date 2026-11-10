<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('qr-groups.index')" wire:navigate>Retour</flux:button>
                <flux:badge color="zinc" size="sm">Groupe QR</flux:badge>
            </div>
            <flux:heading size="xl" level="1">{{ $group->name }}</flux:heading>
            @if ($group->description)
                <flux:subheading>{{ $group->description }}</flux:subheading>
            @endif
            <flux:text size="sm">
                {{ $group->items->count() }} élément(s) · {{ $documents->count() }} document(s) · {{ $clients->count() }} client(s)
                · Créé par {{ $group->user?->name ?? 'utilisateur supprimé' }} le {{ $group->created_at?->format('d/m/Y H:i') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button variant="primary" icon="printer" :href="route('qr-groups.print', $group->public_token)" target="_blank">
                Imprimer tous les QR
            </flux:button>
            @if ($group->user_id === auth()->id() || auth()->user()?->role?->is_system)
                <flux:button variant="danger" icon="trash" wire:click="deleteGroup" wire:confirm="Supprimer ce groupe QR ?">
                    Supprimer
                </flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    <flux:card class="flex flex-col sm:flex-row items-center gap-6">
        <div class="shrink-0 rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700">
            {!! \App\Support\QrCode::svg($groupUrl, 140) !!}
        </div>
        <div class="space-y-2 text-center sm:text-start">
            <flux:heading size="lg">QR du groupe</flux:heading>
            <flux:text size="sm">Ce QR ouvre la page de ce groupe et donne accès à tous les éléments sélectionnés.</flux:text>
            <flux:text size="sm" class="break-all font-mono text-xs">{{ $groupUrl }}</flux:text>
        </div>
    </flux:card>

    @if ($documents->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <flux:heading size="lg">Documents</flux:heading>
                <flux:badge color="zinc" size="sm">{{ $documents->count() }}</flux:badge>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($documents as $item)
                    <div wire:key="group-document-{{ $item->id }}" class="flex gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="shrink-0">
                            {!! \App\Support\QrCode::svg($item->url(), 88) !!}
                        </div>
                        <div class="min-w-0 space-y-1">
                            <flux:badge color="zinc" size="sm">{{ $item->typeLabel() }}</flux:badge>
                            <div class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $item->label }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                @if (data_get($item->meta, 'number'))
                                    N° {{ data_get($item->meta, 'number') }}<br>
                                @endif
                                @if (data_get($item->meta, 'client'))
                                    {{ data_get($item->meta, 'client') }}<br>
                                @endif
                                @if (data_get($item->meta, 'date'))
                                    {{ data_get($item->meta, 'date') }}
                                @endif
                            </div>
                            <flux:button size="sm" variant="ghost" icon="eye" :href="$item->url()" target="_blank">Voir l'archive</flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($clients->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <flux:heading size="lg">Clients</flux:heading>
                <flux:badge color="zinc" size="sm">{{ $clients->count() }}</flux:badge>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($clients as $item)
                    <div wire:key="group-client-{{ $item->id }}" class="flex gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="shrink-0">
                            {!! \App\Support\QrCode::svg($item->url(), 88) !!}
                        </div>
                        <div class="min-w-0 space-y-1">
                            <flux:badge color="zinc" size="sm">{{ $item->typeLabel() }}</flux:badge>
                            <div class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $item->label }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                @if (data_get($item->meta, 'email'))
                                    {{ data_get($item->meta, 'email') }}<br>
                                @endif
                                @if (data_get($item->meta, 'phone'))
                                    {{ data_get($item->meta, 'phone') }}<br>
                                @endif
                                @if (data_get($item->meta, 'city'))
                                    {{ data_get($item->meta, 'city') }}
                                @endif
                            </div>
                            <flux:button size="sm" variant="ghost" icon="eye" :href="$item->url()" target="_blank">Voir l'archive</flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($documents->isEmpty() && $clients->isEmpty())
        <p class="rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700">
            Ce groupe ne contient aucun élément.
        </p>
    @endif
</div>
