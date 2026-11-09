<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">{{ $client->displayName() }}</flux:heading>
            <flux:subheading class="mt-1">{{ $client->isCompany() ? 'Entreprise' : 'Particulier' }}</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('archive')" wire:navigate>Retour</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Informations client</flux:heading>
            <div class="text-sm space-y-1">
                <div><span class="text-zinc-500">Nom :</span> {{ $client->displayName() }}</div>
                <div><span class="text-zinc-500">E-mail :</span> {{ $client->email ?: '—' }}</div>
                <div><span class="text-zinc-500">Téléphone :</span> {{ $client->phone ?: '—' }}</div>
                <div><span class="text-zinc-500">Adresse :</span> {{ $client->fullAddress() ?: '—' }}</div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">QR Code</flux:heading>
            <div class="flex justify-center">
                {!! \App\Support\QrCode::svg(route('archive.clients.show', $client->public_token), 160) !!}
            </div>
        </flux:card>
    </div>

    <flux:card class="space-y-4">
        <flux:heading size="lg">Documents archivés</flux:heading>
        @include('livewire.documents.partials.history-table', [
            'histories' => $client->documentHistories,
            'showInvoice' => true,
            'emptyMessage' => 'Aucun document archivé pour ce client.',
        ])
    </flux:card>
</div>
