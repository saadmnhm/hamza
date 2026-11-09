<?php

namespace App\Models;

use App\Livewire\Documents\DocumentType;
use App\Support\Tva;
use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property CarbonInterface|null $invoice_date
 * @property CarbonInterface|null $due_date
 */
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_DELIVERY_NOTE = 'delivery_note';

    public const TYPE_PURCHASE_ORDER = 'purchase_order';

    public const TYPES = [
        self::TYPE_INVOICE,
        self::TYPE_DELIVERY_NOTE,
        self::TYPE_PURCHASE_ORDER,
    ];

    /** @var array<string, string> */
    public const PREFIXES = [
        self::TYPE_INVOICE => 'FAC',
        self::TYPE_DELIVERY_NOTE => 'BL',
        self::TYPE_PURCHASE_ORDER => 'BC',
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::TYPE_INVOICE => 'Facture',
        self::TYPE_DELIVERY_NOTE => 'Bon de livraison',
        self::TYPE_PURCHASE_ORDER => 'Bon de commande',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'demande_id',
        'type',
        'number',
        'invoice_date',
        'due_date',
        'payment_terms',
        'notes',
        'subtotal',
        'tva_total',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tva_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * The client billed by this invoice.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The Demande this document belongs to.
     *
     * @return BelongsTo<Demande, $this>
     */
    public function Demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    /**
     * The invoice lines.
     *
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    /**
     * Generated document snapshots for this invoice.
     *
     * @return HasMany<DocumentHistory, $this>
     */
    public function documentHistories(): HasMany
    {
        return $this->hasMany(DocumentHistory::class);
    }

    /**
     * Generate the next sequential number for the given document type and year.
     *
     * The first argument may be either a document type or a date string, so
     * that the historical `generateNumber('2026-05-01')` call keeps working.
     */
    public static function generateNumber(?string $typeOrDate = null, ?string $date = null): string
    {
        if ($typeOrDate !== null && str_contains($typeOrDate, '-')) {
            $date = $typeOrDate;
            $type = self::TYPE_INVOICE;
        } else {
            $type = $typeOrDate ?? self::TYPE_INVOICE;
        }

        $prefixBase = self::PREFIXES[$type] ?? 'DOC';
        $year = $date !== null ? Carbon::parse($date)->year : now()->year;
        $prefix = $prefixBase.'-'.$year.'-';

        $lastSequence = static::query()
            ->where('type', $type)
            ->where('number', 'like', $prefix.'%')
            ->pluck('number')
            ->map(fn (string $number): int => (int) substr($number, strlen($prefix)))
            ->max();

        $next = ($lastSequence ?? 0) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate and persist the invoice totals from its lines.
     */
    public function recalculateTotals(): void
    {
        $totals = Tva::sumLines(
            $this->items()->get()->map(fn (InvoiceItem $item): array => [
                'total_ht' => $item->total_ht,
                'tva_amount' => $item->tva_amount,
                'total_ttc' => $item->total_ttc,
            ]),
        );

        $this->forceFill([
            'subtotal' => $totals['subtotal'],
            'tva_total' => $totals['tva_total'],
            'total' => $totals['total'],
        ])->save();
    }

    /**
     * Normalised invoice payload consumed by the existing document template.
     *
     * @return array<string, mixed>
     */
    public function toDocumentData(): array
    {
        $this->loadMissing(['client', 'items']);

        return [
            'invoice_number' => $this->number,
            'document_type' => $this->type,
            'document_title' => self::LABELS[$this->type] ?? 'Document',
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'client' => $this->client?->toDocumentData() ?? [],
            'items' => $this->items->map(fn (InvoiceItem $item): array => [
                'product' => $item->product,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tva_rate' => (float) $item->tva_rate,
                'tva_amount' => (float) $item->tva_amount,
                'total_ht' => (float) $item->total_ht,
                'total_ttc' => (float) $item->total_ttc,
            ])->all(),
            'subtotal' => (float) $this->subtotal,
            'tva_total' => (float) $this->tva_total,
            'tax_amount' => (float) $this->tva_total,
            'total' => (float) $this->total,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
        ];
    }

    /**
     * Render the invoice with the existing template and persist a history row.
     */
    public function generateDocument(int $userId): DocumentHistory
    {
        $token = (string) Str::uuid();

        $data = $this->toDocumentData();

        $html = view('livewire.documents.print.invoice', [
            'data' => $data,
            'company' => Config::query()->first(),
        ])->render();

        $documentType = match ($this->type) {
            self::TYPE_DELIVERY_NOTE => DocumentType::DELIVERY_NOTE,
            self::TYPE_PURCHASE_ORDER => DocumentType::PURCHASE_ORDER,
            default => DocumentType::INVOICE,
        };

        $label = self::LABELS[$this->type] ?? 'Document';

        return $this->documentHistories()->create([
            'user_id' => $userId,
            'public_token' => $token,
            'client_id' => $this->client_id,
            'demande_id' => $this->demande_id,
            'document_type' => $documentType->value,
            'title' => $label.' '.$this->number,
            'data' => $data,
            'html' => $html,
        ]);
    }
}
