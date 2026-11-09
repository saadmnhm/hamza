<?php

namespace App\Models;

use App\Support\Tva;
use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    /** @use HasFactory<InvoiceItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'invoice_id',
        'item_id',
        'position',
        'product',
        'description',
        'quantity',
        'unit_price',
        'tva_rate',
        'tva_amount',
        'total_ht',
        'total_ttc',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'tva_rate' => 'decimal:2',
            'tva_amount' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'total_ttc' => 'decimal:2',
        ];
    }

    /**
     * Always recompute line totals from the shared TVA calculator.
     */
    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item): void {
            $totals = Tva::lineTotals(
                $item->quantity ?? 0,
                $item->unit_price ?? 0,
                $item->tva_rate ?? 0,
            );

            $item->total_ht = $totals['total_ht'];
            $item->tva_amount = $totals['tva_amount'];
            $item->total_ttc = $totals['total_ttc'];
        });
    }

    /**
     * The invoice this line belongs to.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The catalogue item this line was created from, when applicable.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
