<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'unit',
        'unit_price',
        'tva_rate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'tva_rate' => 'decimal:2',
        ];
    }

    /**
     * Invoice lines that reference this catalogue item.
     *
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payload used to pre-fill an invoice line.
     *
     * @return array{product: string, description: string, unit_price: float, tva_rate: float}
     */
    public function toInvoiceLine(): array
    {
        return [
            'product' => $this->name,
            'description' => $this->description ?? '',
            'unit_price' => (float) $this->unit_price,
            'tva_rate' => (float) $this->tva_rate,
        ];
    }
}
