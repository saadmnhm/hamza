<?php

namespace App\Models;

use Database\Factories\DocumentHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentHistory extends Model
{
    /** @use HasFactory<DocumentHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_token',
        'client_id',
        'demande_id',
        'invoice_id',
        'document_type',
        'title',
        'data',
        'html',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DocumentHistory $history): void {
            if (empty($history->public_token)) {
                $history->public_token = (string) Str::uuid();
            }

            if ($history->client_id === null && $history->invoice_id !== null) {
                $history->client_id = Invoice::query()
                    ->whereKey($history->invoice_id)
                    ->value('client_id');
            }

            if ($history->demande_id === null && $history->invoice_id !== null) {
                $history->demande_id = Invoice::query()
                    ->whereKey($history->invoice_id)
                    ->value('demande_id');
            }
        });
    }

    /**
     * The user who generated this document.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The invoice this document was generated from, when applicable.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The Demande this document belongs to, when applicable.
     *
     * @return BelongsTo<Demande, $this>
     */
    public function Demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    /**
     * The client this document belongs to, when applicable.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
