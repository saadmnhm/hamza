<?php

namespace App\Livewire\Documents\Accounting;

use App\Livewire\Documents\DocumentType;
use App\Models\Config;
use App\Models\DocumentHistory;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Invoice extends Component
{
    use WithFileUploads;

    public ?Config $config = null;

    // Document data with validation rules
    #[Rule('required|string|max:50')]
    public $invoice_number;

    #[Rule('required|date')]
    public $invoice_date;

    #[Rule('required|string|max:100')]
    public $client_name;

    #[Rule('required|string|max:255')]
    public $client_address;

    #[Rule('nullable|string|max:20')]
    public $client_vat_number;

    #[Rule('required|string|max:255')]
    public $items_description;

    #[Rule('required|numeric|min:1')]
    public $items_quantity;

    #[Rule('required|numeric|min:0')]
    public $items_unit_price;

    #[Rule('required|numeric|min:0')]
    public $items_total;

    #[Rule('required|numeric|min:0')]
    public $subtotal;

    #[Rule('required|numeric|between:0,100')]
    public $tax_rate;

    #[Rule('required|numeric|min:0')]
    public $tax_amount;

    #[Rule('required|numeric|min:0')]
    public $total;

    #[Rule('required|string|max:100')]
    public $payment_terms;

    #[Rule('nullable|string|max:500')]
    public $notes;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('documents_view'), 403);

        $this->config = Config::first();
    }

    public $generatedHtml = null;

    public $showPreview = false;

    public $historyId = null;

    public function generate(): void
    {
        $validated = $this->validate();

        $html = $this->renderDocument($validated);

        // Save to history
        $history = DocumentHistory::create([
            'user_id' => auth()->id(),
            'document_type' => DocumentType::INVOICE->value,
            'title' => DocumentType::INVOICE->title(),
            'data' => $validated,
            'html' => $html,
        ]);

        // Store for preview
        $this->generatedHtml = $html;
        $this->historyId = $history->id;
        $this->showPreview = true;

        // Dispatch event
        $this->dispatch('documentGenerated', html: $html, title: DocumentType::INVOICE->title(), historyId: $history->id);
    }

    public function printDocument(): void
    {
        if (! $this->generatedHtml) {
            return;
        }

        // Dispatch to JavaScript to open print in new tab
        $this->dispatch('printDocument', html: $this->generatedHtml, title: DocumentType::INVOICE->title());
    }

    public function reprintFromHistory(int $historyId): void
    {
        $history = DocumentHistory::findOrFail($historyId);

        // Fill form data from history
        $this->fill($history->data);

        // Generate new HTML
        $html = $this->renderDocument($history->data);

        // Save new history entry
        $newHistory = DocumentHistory::create([
            'user_id' => auth()->id(),
            'document_type' => DocumentType::INVOICE->value,
            'title' => DocumentType::INVOICE->title(),
            'data' => $history->data,
            'html' => $html,
        ]);

        $this->generatedHtml = $html;
        $this->historyId = $newHistory->id;
        $this->showPreview = true;

        $this->dispatch('documentGenerated', html: $html, title: DocumentType::INVOICE->title(), historyId: $newHistory->id);
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->generatedHtml = null;
    }

    protected function renderDocument(array $data): string
    {
        return view('livewire.documents.print.invoice', [
            'data' => $data,
            'company' => $this->config,
        ])->render();
    }

    public function render()
    {
        $history = DocumentHistory::where('user_id', auth()->id())
            ->where('document_type', DocumentType::INVOICE->value)
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.documents.accounting.invoice', [
            'config' => $this->config,
            'history' => $history,
        ]);
    }
}
