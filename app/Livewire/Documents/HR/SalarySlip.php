<?php

namespace App\Livewire\Documents\HR;

use App\Livewire\Documents\DocumentType;
use App\Models\Config;
use App\Models\DocumentHistory;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class SalarySlip extends Component
{
    use WithFileUploads;

    public ?Config $config = null;

    // Document data with validation rules
    #[Rule('required|string|max:100')]
    public $employee_name;

    #[Rule('nullable|string|max:20')]
    public $employee_matricule;

    #[Rule('required|date')]
    public $period_start;

    #[Rule('required|date')]
    public $period_end;

    #[Rule('required|numeric|min:0')]
    public $basic_salary;

    #[Rule('nullable|numeric|min:0')]
    public $allowances;

    #[Rule('nullable|numeric|min:0')]
    public $deductions;

    #[Rule('required|numeric|min:0')]
    public $net_salary;

    #[Rule('required|string|max:50')]
    public $payment_method;

    #[Rule('required|date')]
    public $payment_date;

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
            'document_type' => DocumentType::SALARY_SLIP->value,
            'title' => DocumentType::SALARY_SLIP->title(),
            'data' => $validated,
            'html' => $html,
        ]);

        // Store for preview
        $this->generatedHtml = $html;
        $this->historyId = $history->id;
        $this->showPreview = true;

        // Dispatch event
        $this->dispatch('documentGenerated', html: $html, title: DocumentType::SALARY_SLIP->title(), historyId: $history->id);
    }

    public function printDocument(): void
    {
        if (! $this->generatedHtml) {
            return;
        }

        // Dispatch to JavaScript to open print in new tab
        $this->dispatch('printDocument', html: $this->generatedHtml, title: DocumentType::SALARY_SLIP->title());
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
            'document_type' => DocumentType::SALARY_SLIP->value,
            'title' => DocumentType::SALARY_SLIP->title(),
            'data' => $history->data,
            'html' => $html,
        ]);

        $this->generatedHtml = $html;
        $this->historyId = $newHistory->id;
        $this->showPreview = true;

        $this->dispatch('documentGenerated', html: $html, title: DocumentType::SALARY_SLIP->title(), historyId: $newHistory->id);
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->generatedHtml = null;
    }

    protected function renderDocument(array $data): string
    {
        return view('livewire.documents.print.salary-slip', [
            'data' => $data,
            'company' => $this->config,
        ])->render();
    }

    public function render()
    {
        $history = DocumentHistory::where('user_id', auth()->id())
            ->where('document_type', DocumentType::SALARY_SLIP->value)
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.documents.hr.salary-slip', [
            'config' => $this->config,
            'history' => $history,
        ]);
    }
}
