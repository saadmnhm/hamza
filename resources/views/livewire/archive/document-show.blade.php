<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">{{ $document->title }}</flux:heading>
            <flux:subheading class="mt-1">{{ $document->document_type }} — {{ $document->created_at->format('d/m/Y H:i') }}</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('archive')" wire:navigate>Retour</flux:button>
            <flux:button variant="primary" icon="printer" :href="route('documents.print', $document)" target="_blank">Imprimer</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Client</flux:heading>
            <div class="text-sm">
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $document->client?->displayName() ?? '—' }}</div>
                <div class="text-zinc-500">{{ $document->client?->fullAddress() ?: '—' }}</div>
                <div class="text-zinc-500">{{ $document->client?->email ?: ($document->client?->phone ?: '—') }}</div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">QR Code</flux:heading>
            <div class="flex justify-center">
                {!! \App\Support\QrCode::svg(route('archive.documents.show', $document->public_token), 160) !!}
            </div>
        </flux:card>
    </div>

    @if ($document->invoice)
        <flux:card class="space-y-2">
            <flux:heading size="lg">Facture associée</flux:heading>
            <div class="text-sm">
                <span class="text-zinc-500">N° :</span> {{ $document->invoice->number }}<br>
                <span class="text-zinc-500">Total TTC :</span> {{ number_format((float) $document->invoice->total, 2, ',', ' ') }} DH
            </div>
        </flux:card>
    @endif
    @if(false)
    <flux:card class="space-y-2">
        <flux:heading size="lg">Aperçu</flux:heading>
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 bg-zinc-50 dark:bg-zinc-800 overflow-auto max-h-96">
            {!! $document->html !!}
        </div>
    </flux:card>
    @endif
</div>
