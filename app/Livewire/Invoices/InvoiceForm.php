<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Demande;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\User;
use App\Support\Tva;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Document commercial')]
class InvoiceForm extends Component
{
    public ?int $invoiceId = null;

    public ?int $demande_id = null;

    public string $client_id = '';

    public string $number = '';

    public string $invoice_date = '';

    public string $due_date = '';

    public string $payment_terms = 'Paiement à 30 jours';

    public string $notes = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?string $type = 'invoice';

    public function mount(?Invoice $invoice = null, ?Client $client = null, ?string $type = null, ?Demande $Demande = null): void
    {
        $this->authorizeAction();

        if ($type !== null && in_array($type, Invoice::TYPES, true)) {
            $this->type = $type;
        }

        if ($invoice !== null) {
            $invoice->load('items');

            $this->invoiceId = $invoice->id;
            $this->type = $invoice->type ?? $this->type;
            $this->demande_id = $invoice->demande_id;
            $this->client_id = (string) $invoice->client_id;
            $this->number = $invoice->number;
            $this->invoice_date = $invoice->invoice_date?->format('Y-m-d') ?? '';
            $this->due_date = $invoice->due_date?->format('Y-m-d') ?? '';
            $this->payment_terms = $invoice->payment_terms ?? '';
            $this->notes = $invoice->notes ?? '';
            $this->items = $invoice->items
                ->map(fn (InvoiceItem $item): array => [
                    'uid' => (string) Str::uuid(),
                    'item_id' => $item->item_id,
                    'product' => $item->product,
                    'description' => $item->description ?? '',
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'tva_rate' => (float) $item->tva_rate,
                ])
                ->values()
                ->all();

            if ($this->items === []) {
                $this->items = [$this->blankItem()];
            }

            return;
        }

        $this->number = Invoice::generateNumber($this->type, null);
        $this->invoice_date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->items = [$this->blankItem()];

        if ($client !== null) {
            $this->client_id = (string) $client->id;
        } else {
            $clientId = request()->integer('client');

            if ($clientId > 0 && Client::query()->whereKey($clientId)->exists()) {
                $this->client_id = (string) $clientId;
            }
        }

        if ($Demande !== null) {
            $this->demande_id = $demande->id;
        } else {
            $demandeId = request()->integer('demande');

            if ($demandeId > 0 && Demande::query()->whereKey($demandeId)->exists()) {
                $this->demande_id = $demandeId;
            }
        }

        $requestType = request()->string('type')->toString();

        if ($requestType !== '' && in_array($requestType, Invoice::TYPES, true)) {
            $this->type = $requestType;
            $this->number = Invoice::generateNumber($this->type, null);
        }
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index]) || count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Pre-fill a line when an article is selected from the catalogue.
     */
    public function updated(string $property, mixed $value): void
    {
        if (! preg_match('/^items\.(\d+)\.item_id$/', $property, $matches)) {
            return;
        }

        $index = (int) $matches[1];

        if (! isset($this->items[$index]) || ! is_scalar($value) || $value === '') {
            return;
        }

        $item = Item::query()->whereKey((int) $value)->first();

        if ($item === null) {
            return;
        }

        $line = $item->toInvoiceLine();

        $this->items[$index]['product'] = $line['product'];
        $this->items[$index]['description'] = $line['description'];
        $this->items[$index]['unit_price'] = $line['unit_price'];
        $this->items[$index]['tva_rate'] = $line['tva_rate'];
    }

    public function save(): void
    {
        $this->authorizeAction();

        $invoice = $this->persist();

        session()->flash('status', 'La facture '.$invoice->number.' a été enregistrée.');

        $this->redirectRoute('documents.show', ['invoice' => $invoice->id], navigate: true);
    }

    public function saveAndPrint(): void
    {
        $this->authorizeAction();

        $invoice = $this->persist();
        $history = $invoice->generateDocument((int) auth()->id());

        $this->redirectRoute('documents.print', ['history' => $history->id], navigate: true);
    }

    public function render(): View
    {
        $clients = Client::query()
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedClient = $this->client_id !== ''
            ? $clients->firstWhere('id', (int) $this->client_id)
            : null;

        $lineTotals = collect($this->items)
            ->map(fn (array $item): array => Tva::lineTotals(
                $item['quantity'] ?? 0,
                $item['unit_price'] ?? 0,
                $item['tva_rate'] ?? 0,
            ))
            ->all();

        return view('livewire.invoices.form', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
            'lineTotals' => $lineTotals,
            'invoiceTotals' => Tva::sumLines($lineTotals),
            'tvaRates' => Tva::options(),
            'catalogItems' => Item::query()->orderBy('name')->get(),
            'productSuggestions' => InvoiceItem::query()
                ->select('product')
                ->distinct()
                ->orderBy('product')
                ->pluck('product'),
        ]);
    }

    private function persist(): Invoice
    {
        if ($this->number === '') {
            $this->number = Invoice::generateNumber($this->type, $this->invoice_date !== '' ? $this->invoice_date : null);
        }

        foreach ($this->items as $index => $item) {
            if (($item['item_id'] ?? '') === '') {
                $this->items[$index]['item_id'] = null;
            }
        }

        $validated = $this->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'number')->ignore($this->invoiceId),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['nullable', 'integer', Rule::exists('items', 'id')],
            'items.*.product' => ['required', 'string', 'max:150'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tva_rate' => ['required', 'numeric', Rule::in(Tva::RATES)],
        ]);

        return DB::transaction(function () use ($validated): Invoice {
            if ($this->demande_id === null && ! empty($validated['client_id'])) {
                $demande = Demande::query()->create([
                    'client_id' => (int) $validated['client_id'],
                    'date' => now()->toDateString(),
                ]);

                $this->demande_id = $demande->id;
            }

            $invoice = $this->invoiceId !== null
                ? Invoice::query()->findOrFail($this->invoiceId)
                : new Invoice;

            $invoice->fill([
                'client_id' => (int) $validated['client_id'],
                'demande_id' => $this->demande_id,
                'type' => $this->type,
                'number' => $validated['number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ])->save();

            $invoice->items()->delete();

            foreach ($validated['items'] as $position => $item) {
                $invoice->items()->create([
                    'position' => $position,
                    'item_id' => $item['item_id'] ?? null,
                    'product' => $item['product'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tva_rate' => $item['tva_rate'],
                ]);
            }

            $invoice->recalculateTotals();

            return $invoice->refresh();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function blankItem(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'item_id' => null,
            'product' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tva_rate' => 20,
        ];
    }

    private function authorizeAction(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission('invoices_create'), 403);
    }
}
