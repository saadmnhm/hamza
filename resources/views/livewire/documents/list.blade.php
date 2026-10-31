<!-- Document Generation Rubrique -->
<div class="mb-8">
    <flux:card>
        <flux:heading size="xl" level="2">Génération de documents</flux:heading>
        <flux:subheading>Créez des documents imprimables professionnels pour vos besoins RH et comptabilité</flux:subheading>
        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 px-4 py-4">
            @foreach ($documentTypes as $type)
            <a
                href="{{ route('documents.' . $type->value) }}"
                wire:navigate
                class="group block rounded-xl border-2 border-transparent hover:border-primary transition-colors duration-150 flex flex-col items-center justify-center"
            >
                <flux:card class="h-full p-6 text-center transition-all duration-150 group-hover:bg-primary/5 group-hover:text-primary">
                    <div class="size-12 mx-auto mb-3 text-primary">
                        <flux:icon :name="$type->icon()" class="size-6" />
                    </div>
                    
                    <flux:heading class="text-sm font-medium mb-1">{{ $type->title() }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500 line-clamp-2">{{ $type->description() }}</flux:text>
                </flux:card>
            </a>
            @endforeach
        </div>
    </flux:card>
</div>