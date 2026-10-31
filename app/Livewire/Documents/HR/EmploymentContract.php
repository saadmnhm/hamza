<?php

namespace App\Livewire\Documents\HR;

use App\Livewire\Documents\DocumentType;
use App\Models\Config;
use App\Models\DocumentHistory;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmploymentContract extends Component
{
    use WithFileUploads;

    public ?Config $config = null;

    // Document data with validation rules
    #[Rule('required|string|max:100')]
    public $employee_name;

    #[Rule('required|string|max:100')]
    public $employee_position;

    #[Rule('required|string|max:50')]
    public $employee_department;

    #[Rule('required|date')]
    public $employee_hire_date;

    #[Rule('required|numeric|min:0')]
    public $employee_salary;

    #[Rule('required|string|max:255')]
    public $employee_address;

    #[Rule('required|string|max:100')]
    public $employer_name;

    #[Rule('required|string|max:255')]
    public $employer_address;

    #[Rule('required|string|max:50')]
    public $contract_duration;

    #[Rule('required|date')]
    public $contract_start_date;

    #[Rule('required|date')]
    public $signing_date;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('documents_view'), 403);

        // Load company config for employer info
        $this->config = Config::first();

        // Pre-fill employer info from company config
        if ($this->config) {
            $this->employer_name = $this->config->raison_sociale;
            $this->employer_address = $this->config->fullAddress();
        }
    }

    public $generatedHtml = null;

    public $showPreview = false;

    public $historyId = null;

    public function generate(): void
    {
        $validated = $this->validate();

        // Generate printable HTML document
        $html = $this->renderDocument($validated);

        // Save to history
        $history = DocumentHistory::create([
            'user_id' => auth()->id(),
            'document_type' => DocumentType::EMPLOYMENT_CONTRACT->value,
            'title' => DocumentType::EMPLOYMENT_CONTRACT->title(),
            'data' => $validated,
            'html' => $html,
        ]);

        // Store for preview
        $this->generatedHtml = $html;
        $this->historyId = $history->id;
        $this->showPreview = true;

        // Dispatch event
        $this->dispatch('documentGenerated', html: $html, title: DocumentType::EMPLOYMENT_CONTRACT->title(), historyId: $history->id);
    }

    public function printDocument(): void
    {
        if (! $this->generatedHtml) {
            return;
        }

        // Dispatch to JavaScript to open print in new tab
        $this->dispatch('printDocument', html: $this->generatedHtml, title: DocumentType::EMPLOYMENT_CONTRACT->title());
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
            'document_type' => DocumentType::EMPLOYMENT_CONTRACT->value,
            'title' => DocumentType::EMPLOYMENT_CONTRACT->title(),
            'data' => $history->data,
            'html' => $html,
        ]);

        $this->generatedHtml = $html;
        $this->historyId = $newHistory->id;
        $this->showPreview = true;

        $this->dispatch('documentGenerated', html: $html, title: DocumentType::EMPLOYMENT_CONTRACT->title(), historyId: $newHistory->id);
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->generatedHtml = null;
    }

    protected function renderDocument(array $data): string
    {
        return view('livewire.documents.print.employmentcontract', [
            'data' => $data,
            'company' => $this->config,
        ])->render();
    }

    public function render()
    {
        $history = DocumentHistory::where('user_id', auth()->id())
            ->where('document_type', DocumentType::EMPLOYMENT_CONTRACT->value)
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.documents.hr.employment-contract', [
            'config' => $this->config,
            'history' => $history,
        ]);
    }
}
