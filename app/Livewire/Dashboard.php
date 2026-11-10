<?php

namespace App\Livewire;

use App\Livewire\Documents\DocumentType;
use App\Models\Client;
use App\Models\Config as CompanyConfig;
use App\Models\Demande;
use App\Models\DocumentHistory;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tableau de bord')]
class Dashboard extends Component
{
    /**
     * French short month labels used by the revenue chart.
     *
     * @var list<string>
     */
    private const MONTH_LABELS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    public function render(): View
    {
        $user = auth()->user();

        $canViewClients = (bool) $user?->hasPermission('clients_view');
        $canViewInvoices = (bool) $user?->hasPermission('invoices_view');
        $canViewDocuments = (bool) $user?->hasPermission('documents_view');
        $canViewUsers = (bool) $user?->hasPermission('users_view');
        $canViewItems = (bool) $user?->hasPermission('items_view');

        $company = CompanyConfig::query()->first();

        $companies = $canViewClients
            ? Client::query()->where('type', Client::TYPE_COMPANY)->count()
            : 0;
        $individuals = $canViewClients
            ? Client::query()->where('type', Client::TYPE_INDIVIDUAL)->count()
            : 0;
        $clientsTotal = $companies + $individuals;

        $invoicesTotal = $canViewInvoices
            ? Invoice::query()->where('type', Invoice::TYPE_INVOICE)->count()
            : 0;
        $revenueTotal = $canViewInvoices
            ? (float) Invoice::query()->where('type', Invoice::TYPE_INVOICE)->sum('total')
            : 0.0;
        $documentsTotal = $canViewDocuments ? DocumentHistory::query()->count() : 0;
        $demandesTotal = ($canViewClients || $canViewDocuments) ? Demande::query()->count() : 0;
        $itemsTotal = $canViewItems ? Item::query()->count() : 0;

        $totalUsers = $canViewUsers ? User::query()->count() : 0;
        $verifiedUsers = $canViewUsers ? User::query()->whereNotNull('email_verified_at')->count() : 0;
        $pendingUsers = max($totalUsers - $verifiedUsers, 0);
        $verificationRate = $totalUsers > 0 ? (int) round($verifiedUsers / $totalUsers * 100) : 0;

        $stats = $this->buildStats(
            canViewClients: $canViewClients,
            canViewInvoices: $canViewInvoices,
            canViewDocuments: $canViewDocuments,
            canViewItems: $canViewItems,
            company: $company,
            companies: $companies,
            individuals: $individuals,
            clientsTotal: $clientsTotal,
            invoicesTotal: $invoicesTotal,
            revenueTotal: $revenueTotal,
            documentsTotal: $documentsTotal,
            demandesTotal: $demandesTotal,
            itemsTotal: $itemsTotal,
            totalUsers: $totalUsers,
            verifiedUsers: $verifiedUsers,
            pendingUsers: $pendingUsers,
            verificationRate: $verificationRate,
        );

        $revenueChart = $canViewInvoices ? $this->buildRevenueChart() : [];

        $documentTypeChart = $canViewDocuments ? $this->buildDocumentTypeChart() : [];

        $topClients = $canViewInvoices ? $this->buildTopClients() : collect();

        $recentDocuments = $canViewDocuments
            ? DocumentHistory::query()->with(['client', 'invoice'])->latest()->take(6)->get()
            : collect();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'company' => $company,
            'revenueChart' => $revenueChart,
            'revenuePeriodTotal' => collect($revenueChart)->sum('value'),
            'documentTypeChart' => $documentTypeChart,
            'documentTypeTotal' => collect($documentTypeChart)->sum('value'),
            'clientTypeChart' => $canViewClients
                ? ['companies' => $companies, 'individuals' => $individuals]
                : null,
            'topClients' => $topClients,
            'recentDocuments' => $recentDocuments,
            'recentUsers' => $canViewUsers
                ? User::query()->latest()->take(5)->get()
                : collect(),
        ]);
    }

    /**
     * @return list<array{label: string, value: int|string, hint: string, icon: string}>
     */
    private function buildStats(
        bool $canViewClients,
        bool $canViewInvoices,
        bool $canViewDocuments,
        bool $canViewItems,
        ?CompanyConfig $company,
        int $companies,
        int $individuals,
        int $clientsTotal,
        int $invoicesTotal,
        float $revenueTotal,
        int $documentsTotal,
        int $demandesTotal,
        int $itemsTotal,
        int $totalUsers,
        int $verifiedUsers,
        int $pendingUsers,
        int $verificationRate,
    ): array {
        $stats = [];

        if ($canViewInvoices) {
            $stats[] = [
                'label' => "Chiffre d'affaires",
                'value' => number_format($revenueTotal, 2, ',', ' ').' DH',
                'hint' => 'Factures TTC cumulées',
                'icon' => 'banknotes',
            ];
            $stats[] = [
                'label' => 'Factures émises',
                'value' => $invoicesTotal,
                'hint' => 'Documents de type facture',
                'icon' => 'document-text',
            ];
        }

        if ($canViewClients) {
            $stats[] = [
                'label' => 'Clients',
                'value' => $clientsTotal,
                'hint' => $companies.' entreprises · '.$individuals.' particuliers',
                'icon' => 'users',
            ];
        }

        if ($canViewDocuments) {
            $stats[] = [
                'label' => 'Documents archivés',
                'value' => $documentsTotal,
                'hint' => 'Toutes catégories confondues',
                'icon' => 'archive-box',
            ];
        }

        if ($canViewClients || $canViewDocuments) {
            $stats[] = [
                'label' => 'Demandes',
                'value' => $demandesTotal,
                'hint' => 'Dossiers clients',
                'icon' => 'folder-open',
            ];
        }

        if ($canViewItems) {
            $stats[] = [
                'label' => 'Articles',
                'value' => $itemsTotal,
                'hint' => 'Catalogue produits & services',
                'icon' => 'cube',
            ];
        }

        $stats[] = [
            'label' => 'Utilisateurs',
            'value' => $totalUsers,
            'hint' => 'Comptes enregistrés',
            'icon' => 'users',
        ];
        $stats[] = [
            'label' => 'Comptes vérifiés',
            'value' => $verifiedUsers,
            'hint' => $totalUsers > 0 ? $verificationRate.' % du total' : 'Aucun compte',
            'icon' => 'check-badge',
        ];
        $stats[] = [
            'label' => 'En attente',
            'value' => $pendingUsers,
            'hint' => 'Vérification e-mail',
            'icon' => 'clock',
        ];
        $stats[] = [
            'label' => 'Fiche entreprise',
            'value' => $company ? 'Configurée' : 'À compléter',
            'hint' => $company?->raison_sociale ?: 'Aucune information renseignée',
            'icon' => 'building-office-2',
        ];

        return $stats;
    }

    /**
     * Monthly revenue for the last twelve months, invoices only.
     *
     * @return list<array{label: string, full_label: string, value: float}>
     */
    private function buildRevenueChart(): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        $end = CarbonImmutable::now()->endOfMonth();

        $invoices = Invoice::query()
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->get(['invoice_date', 'total']);

        $byMonth = $invoices->groupBy(
            fn (Invoice $invoice): string => $invoice->invoice_date?->format('Y-m') ?? 'unknown',
        );

        $chart = [];

        for ($index = 0; $index < 12; $index++) {
            $month = $start->addMonths($index);
            $key = $month->format('Y-m');

            $chart[] = [
                'label' => self::MONTH_LABELS[(int) $month->format('n') - 1],
                'full_label' => self::MONTH_LABELS[(int) $month->format('n') - 1].' '.$month->format('Y'),
                'value' => (float) ($byMonth->get($key)?->sum('total') ?? 0),
            ];
        }

        return $chart;
    }

    /**
     * @return list<array{label: string, value: int, color: string}>
     */
    private function buildDocumentTypeChart(): array
    {
        /** @var Collection<string, int> $counts */
        $counts = DocumentHistory::query()
            ->selectRaw('document_type, count(*) as total')
            ->groupBy('document_type')
            ->pluck('total', 'document_type');

        $colors = [
            DocumentType::INVOICE->value => '#3b82f6',
            DocumentType::DELIVERY_NOTE->value => '#10b981',
            DocumentType::PURCHASE_ORDER->value => '#f59e0b',
            DocumentType::EMPLOYMENT_CONTRACT->value => '#8b5cf6',
            DocumentType::SALARY_SLIP->value => '#f43f5e',
        ];

        $chart = [];

        foreach (DocumentType::cases() as $type) {
            $chart[] = [
                'label' => $type->title(),
                'value' => (int) ($counts[$type->value] ?? 0),
                'color' => $colors[$type->value],
            ];
        }

        return $chart;
    }

    /**
     * @return Collection<int, Client>
     */
    private function buildTopClients(): Collection
    {
        return Client::query()
            ->withSum(
                ['invoices as invoices_sum_total' => fn ($query) => $query->where('type', Invoice::TYPE_INVOICE)],
                'total',
            )
            ->withCount(
                ['invoices as invoices_count' => fn ($query) => $query->where('type', Invoice::TYPE_INVOICE)],
            )
            ->orderByDesc('invoices_sum_total')
            ->take(5)
            ->get()
            ->filter(fn (Client $client): bool => (float) $client->getAttribute('invoices_sum_total') > 0)
            ->values();
    }
}
